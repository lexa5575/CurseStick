<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Coupon;
use Carbon\Carbon;

class ApplyCouponRequest extends FormRequest
{
    /**
     * Validated coupon instance
     */
    protected ?Coupon $coupon_instance = null;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'min:2',
                'max:50',
                function ($attribute, $value, $fail) {
                    $this->validateCouponAvailability($value, $fail);
                },
            ],
        ];
    }

    /**
     * Validate coupon availability and conditions
     */
    protected function validateCouponAvailability($code, $fail)
    {
        $coupon = Coupon::where('code', strtoupper($code))
                        ->where('is_active', true)
                        ->first();

        if (!$coupon) {
            $fail('The selected coupon code is invalid or inactive.');
            return;
        }

        $now = Carbon::now();

        // Check if coupon has started
        if ($coupon->valid_from && $coupon->valid_from->gt($now)) {
            $fail('This coupon is not yet active. Valid from: ' . $coupon->valid_from->format('M j, Y H:i'));
            return;
        }

        // Check if coupon has expired
        if ($coupon->valid_until && $coupon->valid_until->lt($now)) {
            $fail('This coupon has expired on ' . $coupon->valid_until->format('M j, Y H:i'));
            return;
        }

        // Check usage limit
        if ($coupon->usage_limit && $coupon->usage_count >= $coupon->usage_limit) {
            $fail('This coupon has reached its usage limit.');
            return;
        }

        // Check if any coupon is already applied (single coupon logic)
        $appliedCoupons = session('applied_coupons', []);
        if (!empty($appliedCoupons)) {
            $fail('Only one coupon can be applied per order. Please remove the current coupon before applying a new one.');
            return;
        }

        // Check if this specific coupon is already applied
        if (in_array($coupon->id, $appliedCoupons)) {
            $fail('This coupon is already applied to your cart.');
            return;
        }

        // Store coupon for later use in controller
        $this->coupon_instance = $coupon;
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Please enter a coupon code.',
            'code.string' => 'Coupon code must be a valid text.',
            'code.min' => 'Coupon code must be at least 2 characters.',
            'code.max' => 'Coupon code cannot exceed 50 characters.',
            'code.regex' => 'Coupon code can only contain uppercase letters, numbers, underscores, and dashes.',
            'code.exists' => 'This coupon code does not exist or is not active.',
        ];
    }

    /**
     * Get validated coupon model
     */
    public function getCoupon(): ?Coupon
    {
        return $this->coupon_instance ?? null;
    }
}