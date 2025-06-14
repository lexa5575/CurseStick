<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class CouponService
{
    /**
     * Apply coupon(s) to cart and calculate discounts
     */
    public function applyCouponToCart(Coupon $newCoupon, Cart $cart): array
    {
        // Check if coupon is applicable to cart items
        $compatibilityCheck = $this->checkCouponCartCompatibility($newCoupon, $cart);
        
        if (!$compatibilityCheck['compatible']) {
            return [
                'success' => false,
                'message' => $compatibilityCheck['message'],
                'error_type' => 'category_mismatch'
            ];
        }
        
        $appliedCoupons = [$newCoupon]; // Single coupon logic

        // Calculate discounts for all items with the coupon
        $cartCalculation = $this->calculateCartDiscounts($cart, $appliedCoupons);

        // Store applied coupons in session
        $this->storeAppliedCoupons($appliedCoupons);

        return [
            'success' => true,
            'coupon' => $newCoupon,
            'applied_coupons' => $appliedCoupons,
            'calculation' => $cartCalculation,
            'total_savings' => $cartCalculation['total_discount'],
            'final_total' => $cartCalculation['final_total'],
            'item_discounts' => $cartCalculation['item_discounts'],
            'applicable_items_count' => $compatibilityCheck['applicable_items_count']
        ];
    }

    /**
     * Remove specific coupon from cart
     */
    public function removeCouponFromCart(string $couponCode): array
    {
        $appliedCoupons = $this->getAppliedCoupons();
        
        // Remove coupon from applied list
        $appliedCoupons = array_filter($appliedCoupons, function ($coupon) use ($couponCode) {
            $code = is_object($coupon) ? $coupon->code : $coupon['code'];
            return $code !== strtoupper($couponCode);
        });

        // Store updated coupons in session
        $this->storeAppliedCoupons(array_values($appliedCoupons));

        return [
            'success' => true,
            'applied_coupons' => array_values($appliedCoupons),
            'message' => 'Coupon removed successfully'
        ];
    }

    /**
     * Remove all applied coupons from cart
     */
    public function removeAllCoupons(): array
    {
        // Clear all applied coupons from session
        $this->clearCouponData();

        return [
            'success' => true,
            'applied_coupons' => [],
            'message' => 'All coupons removed successfully'
        ];
    }


    /**
     * Get currently applied coupons from session with strict validation
     */
    public function getAppliedCoupons(): array
    {
        $couponIds = Session::get('applied_coupons', []);
        
        if (empty($couponIds)) {
            return [];
        }

        // Get coupons with full validation
        $validCoupons = Coupon::whereIn('id', $couponIds)
                    ->where('is_active', true)
                    ->valid()  // Check valid_from/valid_until
                    ->usable() // Check usage_limit
                    ->get()
                    ->filter(function ($coupon) {
                        return $coupon->isValid(); // Extra check via model method
                    })
                    ->values()
                    ->all();

        // If some coupons became invalid, update session
        $validCouponIds = array_column($validCoupons, 'id');
        $originalIds = $couponIds;
        
        if (array_diff($originalIds, $validCouponIds)) {
            // Some coupons were filtered out - update session
            $this->storeAppliedCoupons($validCoupons);
            
            // Log invalid coupons for debugging
            $invalidIds = array_diff($originalIds, $validCouponIds);
            \Log::warning('Invalid coupons removed from session', [
                'removed_count' => count($invalidIds),
                'valid_count' => count($validCouponIds)
            ]);
        }

        return $validCoupons;
    }

    /**
     * Calculate discounts for entire cart with multiple coupons
     */
    protected function calculateCartDiscounts(Cart $cart, array $coupons): array
    {
        $cartItems = $cart->items()->with('itemable.category')->get();
        $itemDiscounts = [];
        $totalOriginal = 0;
        $totalDiscount = 0;

        foreach ($cartItems as $item) {
            if (!$item->itemable instanceof Product) {
                continue;
            }

            $product = $item->itemable;
            $itemTotal = $item->price * $item->quantity;
            $totalOriginal += $itemTotal;

            $applicableCoupons = $this->getApplicableCouponsForProduct($product, $coupons);
            $itemDiscount = $this->calculateItemDiscount($itemTotal, $applicableCoupons);

            $finalItemTotal = $itemTotal - $itemDiscount;
            
            $itemDiscounts[] = [
                'item_id' => $item->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'category_name' => $product->category->name ?? 'Uncategorized',
                'original_total' => $itemTotal,
                'discount_amount' => $itemDiscount,
                'final_total' => $finalItemTotal,
                'applied_coupons' => array_map(function($coupon) {
                    return [
                        'code' => is_object($coupon) ? $coupon->code : $coupon['code'],
                        'name' => is_object($coupon) ? $coupon->name : $coupon['name'],
                        'discount_type' => is_object($coupon) ? $coupon->discount_type : $coupon['discount_type'],
                        'discount_value' => is_object($coupon) ? $coupon->discount_value : $coupon['discount_value']
                    ];
                }, $applicableCoupons)
            ];

            $totalDiscount += $itemDiscount;
        }

        $finalTotal = max(0, $totalOriginal - $totalDiscount);
        
        return [
            'original_total' => $totalOriginal,
            'total_discount' => $totalDiscount,
            'final_total' => $finalTotal,
            'item_discounts' => $itemDiscounts,
            'applied_coupons_count' => count($coupons)
        ];
    }

    /**
     * Get applicable coupons for a specific product
     */
    protected function getApplicableCouponsForProduct(Product $product, array $coupons): array
    {
        $applicableCoupons = [];

        foreach ($coupons as $coupon) {
            if ($this->isCouponApplicableToProduct($coupon, $product)) {
                $applicableCoupons[] = $coupon;
            }
        }

        return $applicableCoupons;
    }

    /**
     * Check if coupon is applicable to specific product
     */
    protected function isCouponApplicableToProduct($coupon, Product $product): bool
    {
        $couponObj = is_object($coupon) ? $coupon : Coupon::find($coupon['id']);
        
        if (!$couponObj) {
            return false;
        }

        // If coupon applies to all categories
        if ($couponObj->applies_to_all_categories) {
            return true;
        }

        // Check if product's category is in coupon's allowed categories
        $couponCategoryIds = $couponObj->categories()->pluck('categories.id')->toArray();
        return in_array($product->category_id, $couponCategoryIds);
    }

    /**
     * Calculate discount for single item with multiple applicable coupons
     */
    protected function calculateItemDiscount(float $itemTotal, array $applicableCoupons): float
    {
        if (empty($applicableCoupons)) {
            return 0;
        }

        $totalDiscount = 0;
        $remainingAmount = $itemTotal;

        // Sort coupons: fixed amounts first, then percentages
        usort($applicableCoupons, function($a, $b) {
            $aType = is_object($a) ? $a->discount_type : $a['discount_type'];
            $bType = is_object($b) ? $b->discount_type : $b['discount_type'];
            
            if ($aType === 'fixed' && $bType === 'percentage') {
                return -1;
            }
            if ($aType === 'percentage' && $bType === 'fixed') {
                return 1;
            }
            return 0;
        });

        foreach ($applicableCoupons as $coupon) {
            if ($remainingAmount <= 0) {
                break;
            }

            $couponDiscount = 0;
            $discountType = is_object($coupon) ? $coupon->discount_type : $coupon['discount_type'];
            $discountValue = is_object($coupon) ? $coupon->discount_value : $coupon['discount_value'];

            if ($discountType === 'fixed') {
                // Fixed amount discount
                $couponDiscount = min($discountValue, $remainingAmount);
            } elseif ($discountType === 'percentage') {
                // Percentage discount applied to remaining amount
                $couponDiscount = ($remainingAmount * $discountValue) / 100;
            }

            $totalDiscount += $couponDiscount;
            $remainingAmount -= $couponDiscount;
        }

        $calculatedDiscount = min($totalDiscount, $itemTotal);
        
        // Apply floor rounding to ensure price rounds down to whole numbers
        return $this->floorPriceDiscount($calculatedDiscount);
    }

    /**
     * Round discount down to ensure final price is a whole number
     */
    protected function floorPriceDiscount(float $discount): float
    {
        // Round down the discount to ensure the final price is a whole number
        return floor($discount);
    }




    /**
     * Store applied coupons in session
     */
    protected function storeAppliedCoupons(array $coupons): void
    {
        $couponIds = array_map(function($coupon) {
            return is_object($coupon) ? $coupon->id : $coupon['id'];
        }, $coupons);

        Session::put('applied_coupons', $couponIds);
    }

    /**
     * Get cart calculation with current applied coupons
     */
    public function getCurrentCartCalculation(Cart $cart): array
    {
        $appliedCoupons = $this->getAppliedCoupons();
        
        if (empty($appliedCoupons)) {
            $cartItems = $cart->items()->with('itemable')->get();
            $total = $cartItems->sum(function($item) {
                return $item->price * $item->quantity;
            });

            return [
                'original_total' => $total,
                'total_discount' => 0,
                'final_total' => $total,
                'item_discounts' => [],
                'applied_coupons_count' => 0
            ];
        }

        return $this->calculateCartDiscounts($cart, $appliedCoupons);
    }

    /**
     * Validate coupons before checkout and return validation result
     */
    public function validateCouponsForCheckout(): array
    {
        $appliedCoupons = $this->getAppliedCoupons();
        $originalCount = count(Session::get('applied_coupons', []));
        $validCount = count($appliedCoupons);
        
        $result = [
            'valid' => true,
            'coupons' => $appliedCoupons,
            'message' => null
        ];
        
        // Check if some coupons were removed during validation
        if ($originalCount > $validCount) {
            $removedCount = $originalCount - $validCount;
            $result['valid'] = false;
            $result['message'] = $removedCount === $originalCount 
                ? 'All applied coupons have expired or become invalid. Please refresh and try again.'
                : "Some applied coupons have expired or become invalid and were removed. Please review your order.";
        }
        
        // Double-check each coupon
        foreach ($appliedCoupons as $coupon) {
            if (!$coupon->isValid()) {
                $result['valid'] = false;
                $result['message'] = "Coupon '{$coupon->code}' is no longer valid. Please refresh and try again.";
                break;
            }
        }
        
        return $result;
    }

    /**
     * Check if coupon is compatible with cart items
     */
    protected function checkCouponCartCompatibility(Coupon $coupon, Cart $cart): array
    {
        $cartItems = $cart->items()->with('itemable.category')->get();
        $totalItems = $cartItems->count();
        $applicableItems = 0;
        
        // If coupon applies to all categories, it's always compatible
        if ($coupon->applies_to_all_categories) {
            return [
                'compatible' => true,
                'applicable_items_count' => $totalItems,
                'message' => null
            ];
        }
        
        // Get coupon's allowed categories
        $couponCategoryIds = $coupon->categories()->pluck('categories.id')->toArray();
        
        if (empty($couponCategoryIds)) {
            return [
                'compatible' => false,
                'applicable_items_count' => 0,
                'message' => 'This coupon is not configured for any categories.'
            ];
        }
        
        // Check how many items are in compatible categories
        foreach ($cartItems as $item) {
            if ($item->itemable instanceof Product) {
                $product = $item->itemable;
                if ($product->category && in_array($product->category_id, $couponCategoryIds)) {
                    $applicableItems++;
                }
            }
        }
        
        // If no items match the coupon categories
        if ($applicableItems === 0) {
            // Get category names for better error message
            $categoryNames = Category::whereIn('id', $couponCategoryIds)->pluck('name')->toArray();
            $categoriesText = implode(', ', $categoryNames);
            
            return [
                'compatible' => false,
                'applicable_items_count' => 0,
                'message' => "This coupon can only be used for products in: {$categoriesText}. Your cart doesn't contain any items from these categories."
            ];
        }
        
        return [
            'compatible' => true,
            'applicable_items_count' => $applicableItems,
            'message' => null
        ];
    }

    /**
     * Clear all coupon data (useful for checkout completion)
     */
    public function clearCouponData(): void
    {
        Session::forget('applied_coupons');
    }
}