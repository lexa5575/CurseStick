<?php

namespace App\Http\Controllers;

use App\Services\NOWPaymentsService;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Notifications\OrderConfirmation;
use App\Notifications\AdminOrderNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Notification;

class PaymentController extends Controller
{
    protected $nowPaymentsService;

    public function __construct(NOWPaymentsService $nowPaymentsService)
    {
        $this->nowPaymentsService = $nowPaymentsService;
    }

    /**
     * Create a crypto payment invoice
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function createCryptoPayment(Request $request)
    {
        // Validate request data
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|in:USD,EUR,GBP,RUB', // Add more as needed
            'order_id' => 'required|string',
            'callback_url' => 'nullable|url',
            'success_url' => 'nullable|url',
            'cancel_url' => 'nullable|url',
            'order_description' => 'nullable|string|max:500',
        ]);

        try {
            // Create invoice parameters
            $params = [
                'price_amount' => $validated['amount'],
                'price_currency' => $validated['currency'],
                'order_id' => $validated['order_id'],
                'order_description' => $validated['order_description'] ?? null,
                'callback_url' => $validated['callback_url'] ?? route('payment.ipn'),
                'success_url' => $validated['success_url'] ?? route('payment.success'),
                'cancel_url' => $validated['cancel_url'] ?? route('payment.cancel'),
            ];

            // Create invoice via NOWPayments API
            $invoice = $this->nowPaymentsService->createInvoice($params);

            // Invoice data is now stored in database via payment_invoice_id field
            // No need to store sensitive data in session

            // Check if this is an API request
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'invoice_url' => $invoice['invoice_url'],
                    'invoice_id' => $invoice['id'],
                    'order_id' => $validated['order_id'],
                ]);
            }

            // Redirect to payment page
            return redirect()->away($invoice['invoice_url']);

        } catch (\Exception $e) {
            Log::error('Failed to create crypto payment', [
                'error' => $e->getMessage(),
                'order_id' => $validated['order_id'] ?? null,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create payment. Please try again.',
                    'error' => config('app.debug') ? $e->getMessage() : null,
                ], 500);
            }

            return back()->with('error', 'Failed to create payment. Please try again.');
        }
    }

    /**
     * Handle IPN (Instant Payment Notification) callback from NOWPayments
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleIPN(Request $request)
    {
        // Log only essential IPN info, not sensitive data
        Log::info('NOWPayments IPN received', [
            'payment_status' => $request->input('payment_status'),
            'order_id' => $request->input('order_id'),
            'invoice_id' => $request->input('invoice_id'),
            'payment_id' => $request->input('payment_id'),
            'timestamp' => now()->toISOString(),
            'ip_address' => $request->ip(),
            // DO NOT log full request data for security
        ]);

        // Verify IPN signature if configured
        if (config('nowpayments.ipn_secret')) {
            $providedSig = $request->header('x-nowpayments-sig');
            $calculatedSig = hash_hmac('sha512', $request->getContent(), config('nowpayments.ipn_secret'));
            
            if ($providedSig !== $calculatedSig) {
                Log::warning('Invalid NOWPayments IPN signature');
                return response()->json(['error' => 'Invalid signature'], 400);
            }
        }

        // Process the payment status update
        $paymentStatus = $request->input('payment_status');
        $orderId = $request->input('order_id');
        $invoiceId = $request->input('invoice_id');

        // Handle different payment statuses
        switch ($paymentStatus) {
            case 'confirmed':
            case 'finished':
                // Payment successful
                $this->handleSuccessfulPayment($orderId, $invoiceId, $request->all());
                break;
                
            case 'partially_paid':
                // Partial payment received
                $this->handlePartialPayment($orderId, $invoiceId, $request->all());
                break;
                
            case 'expired':
            case 'failed':
                // Payment failed
                $this->handleFailedPayment($orderId, $invoiceId, $request->all());
                break;
        }

        return response()->json(['success' => true]);
    }

    /**
     * Handle successful payment
     */
    protected function handleSuccessfulPayment($orderId, $invoiceId, $data)
    {
        Log::info('Payment successful', [
            'order_id' => $orderId,
            'invoice_id' => $invoiceId,
            'payment_status' => $data['payment_status'] ?? null,
            'actually_paid' => $data['actually_paid'] ?? null,
            'pay_amount' => $data['pay_amount'] ?? null,
            'pay_currency' => $data['pay_currency'] ?? null,
            'timestamp' => now()->toISOString(),
            // DO NOT log full IPN data for security
        ]);

        // Find the order and verify invoice ID matches
        $order = Order::where('id', $orderId)
                     ->where('payment_invoice_id', $invoiceId)
                     ->first();
        
        if (!$order) {
            Log::error('Order not found or invoice ID mismatch for successful payment', [
                'order_id' => $orderId,
                'invoice_id' => $invoiceId
            ]);
            return;
        }

        // Update order status
        $order->status = 'Оплачен';
        $order->payment_status = 'completed';
        $order->save();

        // Add entry to status history
        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status' => 'Paid',
            'comment' => 'Payment received via cryptocurrency'
        ]);

        // Send email notifications
        try {
            // Get coupon information from order comment if available
            $calculation = null;
            $appliedCoupons = [];
            
            // Try to extract coupon info from order comment
            if ($order->comment && str_contains($order->comment, 'Applied coupon:')) {
                // For crypto payments, we don't have access to the original coupon objects
                // but we can show the discount amount from the order comment
                $calculation = [
                    'original_total' => $order->total, // We don't have the original total
                    'total_discount' => 0 // We don't have the exact discount amount
                ];
            }
            
            // Notification to client
            if ($order->email) {
                Notification::route('mail', $order->email)
                    ->notify(new OrderConfirmation($order, $calculation, $appliedCoupons));
            }
            
            // Notification to administrator
            $adminEmail = config('mail.admin_address', 'admin@crusestick.com');
            Notification::route('mail', $adminEmail)
                ->notify(new AdminOrderNotification($order, $adminEmail, $calculation, $appliedCoupons));
                
        } catch (\Exception $emailException) {
            Log::error('Error sending payment confirmation emails', [
                'error' => $emailException->getMessage(),
                'order_id' => $orderId
            ]);
        }
    }

    /**
     * Handle partial payment
     */
    protected function handlePartialPayment($orderId, $invoiceId, $data)
    {
        Log::warning('Partial payment received', [
            'order_id' => $orderId,
            'invoice_id' => $invoiceId,
            'payment_status' => $data['payment_status'] ?? null,
            'actually_paid' => $data['actually_paid'] ?? null,
            'pay_amount' => $data['pay_amount'] ?? null,
            'pay_currency' => $data['pay_currency'] ?? null,
            'timestamp' => now()->toISOString(),
            // DO NOT log full IPN data for security
        ]);

        $order = Order::where('id', $orderId)
                     ->where('payment_invoice_id', $invoiceId)
                     ->first();
        
        if ($order) {
            $order->payment_status = 'partial';
            $order->save();
            
            // Add entry to status history with actual payment amount
            $actuallyPaid = $data['actually_paid'] ?? 'unknown';
            $payCurrency = $data['pay_currency'] ?? 'crypto';
            
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => 'Partial Payment',
                'comment' => "Partial payment received. Amount: {$actuallyPaid} {$payCurrency}. Remaining balance needs to be settled."
            ]);
        }
    }

    /**
     * Handle failed payment
     */
    protected function handleFailedPayment($orderId, $invoiceId, $data)
    {
        Log::error('Payment failed', [
            'order_id' => $orderId,
            'invoice_id' => $invoiceId,
            'payment_status' => $data['payment_status'] ?? null,
            'failure_reason' => $data['outcome']['reason'] ?? null,
            'timestamp' => now()->toISOString(),
            // DO NOT log full IPN data for security
        ]);

        $order = Order::where('id', $orderId)
                     ->where('payment_invoice_id', $invoiceId)
                     ->first();
        
        if ($order) {
            $order->payment_status = 'failed';
            $order->save();
            
            // Get failure reason for better tracking
            $failureReason = $data['outcome']['reason'] ?? 'Payment expired or failed';
            $paymentStatus = $data['payment_status'] ?? 'unknown';
            
            // Add entry to status history
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => 'Payment Failed',
                'comment' => "Payment {$paymentStatus}. Reason: {$failureReason}"
            ]);
        }
    }

    /**
     * Universal payment status page - redirects based on actual payment status
     */
    public function paymentSuccess(Request $request)
    {
        $token = $request->get('token');
        $order = null;
        
        if ($token) {
            $order = Order::where('payment_token', $token)->first();
            
            // Additional security check for authenticated users
            if ($order && auth()->check() && $order->user_id !== auth()->id()) {
                abort(403, 'Access denied to this order');
            }
        }
        
        if (!$order) {
            abort(404, 'Order not found');
        }
        
        // Redirect based on actual payment status
        switch ($order->payment_status) {
            case 'completed':
                // Payment fully completed
                return view('payment.success', compact('order'));
                
            case 'partial':
                // Partial payment received
                $actuallyPaid = $request->get('actually_paid');
                return view('payment.partial', compact('order', 'actuallyPaid'));
                
            case 'failed':
            case 'expired':
                // Payment failed or expired
                return view('payment.failed', compact('order'));
                
            case 'cancelled':
                // Payment cancelled by user
                return redirect()->route('payment.cancel', ['token' => $token]);
                
            case 'waiting':
            default:
                // Still waiting for payment or unknown status
                return view('payment.success', compact('order'));
        }
    }

    /**
     * Payment partial page
     */
    public function paymentPartial(Request $request)
    {
        $token = $request->get('token');
        $order = null;
        $actuallyPaid = $request->get('actually_paid');
        
        if ($token) {
            $order = Order::where('payment_token', $token)->first();
            
            // Additional security check for authenticated users
            if ($order && auth()->check() && $order->user_id !== auth()->id()) {
                abort(403, 'Access denied to this order');
            }
        }
        
        if (!$order) {
            abort(404, 'Order not found');
        }
        
        return view('payment.partial', compact('order', 'actuallyPaid'));
    }

    /**
     * Payment failed page
     */
    public function paymentFailed(Request $request)
    {
        $token = $request->get('token');
        $order = null;
        
        if ($token) {
            $order = Order::where('payment_token', $token)->first();
            
            // Additional security check for authenticated users
            if ($order && auth()->check() && $order->user_id !== auth()->id()) {
                abort(403, 'Access denied to this order');
            }
        }
        
        if (!$order) {
            abort(404, 'Order not found');
        }
        
        return view('payment.failed', compact('order'));
    }

    /**
     * Payment cancel page
     */
    public function paymentCancel(Request $request)
    {
        $token = $request->get('token');
        $order = null;
        
        if ($token) {
            $order = Order::where('payment_token', $token)->first();
            
            // Additional security check for authenticated users
            if ($order && auth()->check() && $order->user_id !== auth()->id()) {
                abort(403, 'Access denied to this order');
            }
            
            // Update order status to "Cancelled" if payment was cancelled
            if ($order && $order->payment_status === 'waiting') {
                $order->status = 'Cancelled';
                $order->payment_status = 'cancelled';
                $order->save();
                
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status' => 'Cancelled',
                    'comment' => 'Payment cancelled by user'
                ]);
            }
        }
        
        if (!$order) {
            abort(404, 'Order not found');
        }
        
        return view('payment.cancel', compact('order'));
    }

    /**
     * Get available crypto currencies
     */
    public function getAvailableCurrencies()
    {
        try {
            $currencies = $this->nowPaymentsService->getAvailableCurrencies();
            
            return response()->json([
                'success' => true,
                'currencies' => $currencies
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch currencies'
            ], 500);
        }
    }
} 