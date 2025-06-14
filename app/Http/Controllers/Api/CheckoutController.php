<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\HasCartAccess;
use App\Http\Requests\Checkout\ProcessCheckoutRequest;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Notifications\OrderConfirmation;
use App\Notifications\AdminOrderNotification;
use App\Services\NOWPaymentsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    use HasCartAccess;
    // NOWPaymentsService will be loaded only when needed for crypto payments

    /**
     * Get data for checkout form
     * Route: GET /api/checkout/data
     */
    public function getData()
    {
        // Get cart using unified logic
        $cart = $this->getUserCart();

        $cartItems = [];
        $total = 0;

        if ($cart) {
            $items = $cart->items()->with('itemable')->get();

            foreach ($items as $item) {
                if ($item->itemable instanceof Product) {
                    $cartItems[] = [
                        'id' => $item->id,
                        'product' => [
                            'id' => $item->itemable->id,
                            'name' => $item->itemable->name,
                            'image' => $item->itemable->image ?? '/images/placeholder.jpg',
                        ],
                        'quantity' => $item->quantity,
                        'price' => (float) $item->price,
                        'total' => (float) ($item->quantity * $item->price),
                    ];
                    $total += $item->quantity * $item->price;
                }
            }
        }

        // User data if authenticated
        $userData = [];
        if (Auth::check()) {
            $user = Auth::user();
            $userData = [
                'name' => $user->first_name . ' ' . $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone ?? '',
            ];
        }

        // Available payment methods
        $paymentMethods = [
            ['id' => 'zelle', 'name' => 'Zelle (Instant bank transfers)'],
            ['id' => 'crypto', 'name' => 'Cryptocurrency (Bitcoin, Ethereum, etc.)'],
        ];

        return response()->json([
            'cartItems' => $cartItems,
            'total' => (float) $total,
            'isEmpty' => count($cartItems) === 0,
            'userData' => $userData,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    /**
     * Process checkout order
     * Route: POST /api/checkout/process
     */
    public function process(ProcessCheckoutRequest $request)
    {
        // Log the request for debugging
        \Log::info('Checkout process started', [
            'method' => $request->method(),
            'payment_method' => $request->input('payment_method'),
            'name' => $request->input('name'),
            'email' => $request->input('email')
        ]);

        // Data already validated by ProcessCheckoutRequest

        // Get cart using unified logic
        $cart = $this->getUserCart();
        \Log::info('Cart retrieved', ['cart_id' => $cart ? $cart->id : null, 'items_count' => $cart ? $cart->items()->count() : 0]);

        // If cart is empty or not found
        if (!$cart || $cart->items()->count() == 0) {
            \Log::warning('Cart is empty or not found');
            return response()->json([
                'success' => false,
                'message' => 'Your cart is empty'
            ], 400);
        }

        try {
            // Begin database transaction
            DB::beginTransaction();

            // Determine initial order status based on payment method
            $orderStatus = 'New';
            $paymentStatus = 'pending';

            if ($request->payment_method === 'crypto') {
                $orderStatus = 'Awaiting Payment';
                $paymentStatus = 'waiting';
            }

            // Create order using only fillable fields
            $order = new Order([
                'status' => $orderStatus,
                'company' => $request->company,
                'street' => $request->street,
                'house' => $request->house ?? '',
                'city' => $request->city,
                'state' => $request->state,
                'postal_code' => $request->postal_code,
                'country' => $request->country,
                'phone' => $request->phone,
                'email' => $request->email,
                'name' => $request->name,
                'comment' => $request->comment,
                'payment_method' => $request->payment_method ?? 'none',
            ]);

            // Validate coupons before processing checkout
            $couponService = app(\App\Services\CouponService::class);
            $couponValidation = $couponService->validateCouponsForCheckout();
            
            // If coupons are invalid, return error
            if (!$couponValidation['valid']) {
                DB::rollBack();
                
                \Log::warning('Checkout failed due to invalid coupons', [
                    'message' => $couponValidation['message']
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => $couponValidation['message'],
                    'coupon_error' => true // Flag to indicate coupon-related error
                ], 400);
            }
            
            // Calculate total with validated coupons
            $calculation = $couponService->getCurrentCartCalculation($cart);
            $appliedCoupons = $couponValidation['coupons'];
            
            // Add coupon info to comment
            $comment = $request->comment ?? '';
            if (!empty($appliedCoupons)) {
                $couponInfo = [];
                foreach ($appliedCoupons as $coupon) {
                    $couponInfo[] = "Applied coupon: {$coupon->code} (-{$calculation['total_discount']} USD)";
                }
                $comment = trim($comment . "\n\n" . implode("\n", $couponInfo));
            }
            
            // Set system fields separately (not in fillable for security)
            $order->user_id = Auth::check() ? Auth::id() : null;
            $order->total = $calculation['final_total']; // Use final total with discounts
            $order->payment_status = $paymentStatus;
            $order->comment = $comment; // Update comment with coupon info

            $order->save();

            // Create order items from cart
            foreach ($cart->items as $item) {
                if ($item->itemable instanceof Product) {
                    $orderItem = new OrderItem([
                        'order_id' => $order->id,
                        'product_id' => $item->itemable->id,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'discount' => 0, // Default no discount
                        'product_name' => $item->itemable->name,
                        'product_sku' => $item->itemable->sku ?? null,
                    ]);
                    $orderItem->save();
                }
            }

            // Add first entry to status history
            $statusHistory = new OrderStatusHistory([
                'order_id' => $order->id,
                'status' => $orderStatus,
            ]);
            $statusHistory->save();

            // If cryptocurrency payment is selected, create invoice through NOWPayments
            if ($request->payment_method === 'crypto') {
                try {
                    // Load NOWPaymentsService only when needed
                    $nowPaymentsService = app(NOWPaymentsService::class);
                    
                    $invoice = $nowPaymentsService->createInvoice([
                        'price_amount' => $order->total,
                        'price_currency' => 'USD',
                        'order_id' => (string)$order->id,
                        'order_description' => 'Order #' . $order->order_number . ' from CruseStick Store',
                        'callback_url' => route('payment.ipn'),
                        'success_url' => route('payment.success', ['token' => $order->payment_token]),
                        'cancel_url' => route('payment.cancel', ['token' => $order->payment_token]),
                    ]);

                    // Save invoice ID in order for tracking
                    $order->payment_invoice_id = $invoice['id'] ?? null;
                    $order->save();

                    // Increment coupon usage count (with race condition protection)
                    foreach ($appliedCoupons as $coupon) {
                        $usageIncremented = $coupon->incrementUsage();
                        if (!$usageIncremented) {
                            // Coupon usage limit was exceeded during checkout
                            DB::rollBack();
                            \Log::warning('Crypto checkout failed: coupon usage limit exceeded', [
                                'coupon_id' => $coupon->id,
                                'coupon_code' => $coupon->code,
                                'usage_count' => $coupon->usage_count,
                                'usage_limit' => $coupon->usage_limit
                            ]);
                            
                            return response()->json([
                                'success' => false,
                                'message' => "Coupon '{$coupon->code}' has reached its usage limit. Please try again without this coupon.",
                                'coupon_error' => true
                            ], 400);
                        }
                    }

                    // Clear cart and coupons
                    $cart->items()->delete();
                    $couponService->clearCouponData();

                    // Commit transaction
                    DB::commit();

                    // Return URL for redirecting to payment page
                    return response()->json([
                        'success' => true,
                        'message' => 'Redirecting to payment page...',
                        'order_id' => (string)$order->id,
                        'redirect_url' => $invoice['invoice_url'],
                        'payment_type' => 'crypto'
                    ]);
                } catch (\Exception $paymentException) {
                    // If failed to create invoice, rollback transaction
                    DB::rollBack();

                    // Log the specific error for debugging
                    \Log::error('Crypto payment processing failed: ' . $paymentException->getMessage(), [
                        'order_id' => $order->id ?? null,
                        'payment_method' => 'crypto'
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to create payment invoice. Please try again.',
                        'error' => $paymentException->getMessage()
                    ], 500);
                }
            }

            // For other payment methods - standard processing
            // Increment coupon usage count (with race condition protection)
            foreach ($appliedCoupons as $coupon) {
                $usageIncremented = $coupon->incrementUsage();
                if (!$usageIncremented) {
                    // Coupon usage limit was exceeded during checkout
                    DB::rollBack();
                    \Log::warning('Checkout failed: coupon usage limit exceeded', [
                        'coupon_id' => $coupon->id,
                        'coupon_code' => $coupon->code,
                        'usage_count' => $coupon->usage_count,
                        'usage_limit' => $coupon->usage_limit
                    ]);
                    
                    return response()->json([
                        'success' => false,
                        'message' => "Coupon '{$coupon->code}' has reached its usage limit. Please try again without this coupon.",
                        'coupon_error' => true
                    ], 400);
                }
            }
            
            // Clear cart and coupons
            $cart->items()->delete();
            $couponService->clearCouponData();

            // If everything is successful, commit transaction
            DB::commit();

            // Send notification to client (only for non-crypto payments)
            try {
                if ($order->email) {
                    Notification::route('mail', $order->email)
                        ->notify(new OrderConfirmation($order, $calculation, $appliedCoupons));
                }

                // Send notification to administrator
                $adminEmail = config('mail.admin_address', 'admin@crusestick.com');
                Notification::route('mail', $adminEmail)
                    ->notify(new AdminOrderNotification($order, $adminEmail, $calculation, $appliedCoupons));
            } catch (\Exception $emailException) {
                // Log sending error, but don't interrupt order process
                \Log::error('Email notification sending error: ' . $emailException->getMessage());
            }

            // Ensure order_id is passed correctly in string format to avoid type conversion issues
            return response()->json([
                'success' => true,
                'message' => 'Your order has been successfully placed!',
                'order_id' => (string)$order->id,
                'redirect_url' => '/orders/' . $order->id . '/confirmation'
            ]);
        } catch (\Exception $e) {
            // If an error occurred, rollback transaction
            DB::rollBack();

            \Log::error('Checkout process failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your order. Please try again later.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
