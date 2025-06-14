<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Корзина доступна всем (гостям и пользователям)
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'quantity' => [
                'sometimes',
                'integer',
                'min:1',
                'max:100'
            ]
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'quantity.min' => 'The quantity must be greater than 0',
            'quantity.max' => 'Maximum quantity of goods: 100',
            'quantity.integer' => 'The quantity must be an integer.'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Устанавливаем значение по умолчанию, если quantity не передан
        $this->merge([
            'quantity' => $this->quantity ?? 1
        ]);
    }

    /**
     * Get the validated data from the request.
     * Переопределяем чтобы всегда возвращать quantity
     */
    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated($key, $default);
        
        // Гарантируем что quantity всегда есть
        if (!isset($validated['quantity'])) {
            $validated['quantity'] = 1;
        }
        
        return $validated;
    }
}