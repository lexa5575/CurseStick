<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApplyCouponRequest;
use App\Traits\HasCartAccess;
use App\Services\CouponService;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CouponController extends Controller
{
    use HasCartAccess;
    
    protected CouponService $couponService;

    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }

    /**
     * Apply coupon to cart
     * 
     * @param ApplyCouponRequest $request
     * @return JsonResponse
     */
    public function applyCoupon(ApplyCouponRequest $request): JsonResponse
    {
        try {
            // Get user's cart
            $cart = $this->getUserCart();
            
            if (!$cart || $cart->items()->count() === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your cart is empty. Add some products before applying coupons.',
                ], 400);
            }

            // Get validated coupon from request
            $coupon = $request->getCoupon();
            
            if (!$coupon) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid coupon code.',
                ], 400);
            }

            // Apply coupon using service
            $result = $this->couponService->applyCouponToCart($coupon, $cart);

            // Check if coupon application was successful
            if (!$result['success']) {
                // Handle different error types
                $errorType = $result['error_type'] ?? 'general';
                $statusCode = $errorType === 'category_mismatch' ? 422 : 400;
                
                Log::warning('Coupon application failed', [
                    'error_type' => $errorType,
                    'message' => $result['message'],
                    'user_id' => Auth::id(),
                    'cart_id' => $cart->id
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'error_type' => $errorType
                ], $statusCode);
            }

            // Log successful coupon application
            Log::info('Coupon applied successfully', [
                'user_id' => Auth::id(),
                'cart_id' => $cart->id,
                'savings' => $result['total_savings'],
                'applicable_items' => $result['applicable_items_count'] ?? null
            ]);

            $responseMessage = "Coupon '{$coupon->code}' applied successfully!";
            
            // Add info about partial application if applicable
            if (isset($result['applicable_items_count']) && $result['applicable_items_count'] < $cart->items()->count()) {
                $responseMessage .= " This coupon applies to {$result['applicable_items_count']} items in your cart.";
            }

            return response()->json([
                'success' => true,
                'message' => $responseMessage,
                'coupon' => [
                    'code' => $coupon->code,
                    'name' => $coupon->name,
                    'discount_type' => $coupon->discount_type,
                    'discount_value' => $coupon->discount_value,
                    'formatted_discount' => $coupon->formatted_discount,
                ],
                'savings' => floor($result['total_savings']),
                'calculation' => [
                    'original_total' => (float) $result['calculation']['original_total'],
                    'total_discount' => floor($result['calculation']['total_discount']),
                    'final_total' => (float) $result['calculation']['final_total'],
                ],
                'item_discounts' => $this->formatItemDiscounts($result['calculation']['item_discounts']),
                'applied_coupons_count' => count($result['applied_coupons']),
                'applicable_items_count' => $result['applicable_items_count'] ?? null
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to apply coupon', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to process coupon request. Please try again.',
            ], 400);
        }
    }

    /**
     * Remove specific coupon from cart
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function removeCoupon(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'code' => 'required|string|max:50'
            ]);

            $result = $this->couponService->removeCouponFromCart($request->input('code'));

            // Get updated cart calculation
            $cart = $this->getUserCart();
            $calculation = $this->couponService->getCurrentCartCalculation($cart);

            Log::info('Coupon removed successfully', [
                'user_id' => Auth::id(),
                'remaining_coupons' => count($result['applied_coupons'])
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Coupon removed successfully',
                'calculation' => [
                    'original_total' => (float) $calculation['original_total'],
                    'total_discount' => floor($calculation['total_discount']),
                    'final_total' => (float) $calculation['final_total'],
                ],
                'applied_coupons_count' => $calculation['applied_coupons_count']
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to remove coupon', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove coupon. Please try again.',
            ], 500);
        }
    }

    /**
     * Remove all coupons from cart
     * 
     * @return JsonResponse
     */
    public function removeAllCoupons(): JsonResponse
    {
        try {
            $result = $this->couponService->removeAllCoupons();

            // Get updated cart calculation
            $cart = $this->getUserCart();
            $calculation = $this->couponService->getCurrentCartCalculation($cart);

            Log::info('All coupons removed', [
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'All coupons removed successfully',
                'calculation' => [
                    'original_total' => (float) $calculation['original_total'],
                    'total_discount' => floor($calculation['total_discount']),
                    'final_total' => (float) $calculation['final_total'],
                ],
                'applied_coupons_count' => 0
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to remove all coupons', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove coupons. Please try again.',
            ], 500);
        }
    }

    /**
     * Get current cart calculation with applied coupons
     * 
     * @return JsonResponse
     */
    public function getCartCalculation(): JsonResponse
    {
        try {
            $cart = $this->getUserCart();
            
            if (!$cart) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart not found',
                ], 404);
            }

            $calculation = $this->couponService->getCurrentCartCalculation($cart);
            $appliedCoupons = $this->couponService->getAppliedCoupons();

            return response()->json([
                'success' => true,
                'calculation' => [
                    'original_total' => (float) $calculation['original_total'],
                    'total_discount' => floor($calculation['total_discount']),
                    'final_total' => (float) $calculation['final_total'],
                ],
                'item_discounts' => $this->formatItemDiscounts($calculation['item_discounts'] ?? []),
                'applied_coupons' => array_map(function($coupon) {
                    return [
                        'code' => $coupon['code'],
                        'name' => $coupon['name'],
                        'discount_type' => $coupon['discount_type'],
                        'discount_value' => $coupon['discount_value'],
                    ];
                }, $appliedCoupons),
                'applied_coupons_count' => count($appliedCoupons)
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get cart calculation', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate cart totals',
            ], 500);
        }
    }


    /**
     * Format item discounts for response
     * 
     * @param array $itemDiscounts
     * @return array
     */
    protected function formatItemDiscounts(array $itemDiscounts): array
    {
        return array_map(function($item) {
            return [
                'item_id' => $item['item_id'],
                'product_id' => $item['product_id'],
                'product_name' => $item['product_name'],
                'category_name' => $item['category_name'],
                'original_total' => (float) $item['original_total'],
                'discount_amount' => floor($item['discount_amount']),
                'final_total' => (float) $item['final_total'],
                'savings_percentage' => $item['original_total'] > 0 
                    ? round(($item['discount_amount'] / $item['original_total']) * 100, 1)
                    : 0,
                'applied_coupons' => $item['applied_coupons'] ?? []
            ];
        }, $itemDiscounts);
    }
}