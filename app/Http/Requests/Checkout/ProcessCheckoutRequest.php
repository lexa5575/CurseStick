<?php

namespace App\Http\Requests\Checkout;

use Illuminate\Foundation\Http\FormRequest;

class ProcessCheckoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Все пользователи могут использовать эту форму заказа
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Regular expression for English letters, numbers and basic special characters
        $englishRegex = '/^[A-Za-z0-9\s\-\.\,\#\&\/\(\)]*$/';
        
        // Valid US states list
        $validStates = [
            'Alabama', 'Alaska', 'Arkansas', 'California', 'Colorado', 'Connecticut', 'Delaware', 
            'Florida', 'Georgia', 'Hawaii', 'Idaho', 'Illinois', 'Indiana', 'Iowa', 'Kansas', 
            'Kentucky', 'Louisiana', 'Maine', 'Maryland', 'Massachusetts', 'Michigan', 'Minnesota', 
            'Mississippi', 'Missouri', 'Montana', 'Nebraska', 'Nevada', 'New Hampshire', 'New Jersey', 
            'New Mexico', 'New York', 'North Carolina', 'North Dakota', 'Ohio', 'Oklahoma', 'Oregon', 
            'Pennsylvania', 'Rhode Island', 'South Carolina', 'South Dakota', 'Tennessee', 'Texas', 
            'Utah', 'Vermont', 'Virginia', 'Washington', 'West Virginia', 'Wisconsin', 'Wyoming'
        ];
        
        return [
            'name' => ['required', 'string', 'min:1', 'max:255', 'regex:' . $englishRegex],
            'email' => ['required', 'email:rfc,dns', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[\+]?[0-9\s\-\(\)]*$/'],
            'company' => ['nullable', 'string', 'max:255', 'regex:' . $englishRegex],
            'street' => ['required', 'string', 'min:5', 'max:500', 'regex:' . $englishRegex],
            'house' => ['nullable', 'string', 'max:255', 'regex:' . $englishRegex],
            'city' => ['required', 'string', 'min:2', 'max:255', 'regex:' . $englishRegex],
            'state' => ['required', 'string', 'in:' . implode(',', $validStates)],
            'postal_code' => ['required', 'string', 'min:5', 'max:20', 'regex:/^[A-Za-z0-9\-\s]*$/'],
            'country' => ['required', 'string', 'in:United States'],
            'payment_method' => ['required', 'string', 'in:zelle,crypto'],
            'comment' => ['nullable', 'string', 'max:1000'],
            'ageVerified' => ['required', 'boolean', 'accepted'],
        ];
    }
    
    /**
     * Custom validation error messages
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // Required field messages
            'name.required' => 'Full name is required',
            'name.min' => 'Name must be at least 1 character long',
            'name.max' => 'Name cannot exceed 255 characters',
            'name.regex' => 'Name must contain only English letters, numbers, and basic symbols (. , # & / ( ) -)',
            
            'email.required' => 'Email address is required',
            'email.email' => 'Please enter a valid email address',
            'email.max' => 'Email address cannot exceed 255 characters',
            
            'phone.max' => 'Phone number cannot exceed 20 characters',
            'phone.regex' => 'Phone number can only contain numbers, spaces, dashes, parentheses, and plus sign',
            
            'company.max' => 'Company name cannot exceed 255 characters',
            'company.regex' => 'Company name must contain only English letters, numbers, and basic symbols (. , # & / ( ) -)',
            
            'street.required' => 'Street address is required',
            'street.min' => 'Street address must be at least 5 characters long',
            'street.max' => 'Street address cannot exceed 500 characters',
            'street.regex' => 'Street address must contain only English letters, numbers, and basic symbols (. , # & / ( ) -)',
            
            'house.max' => 'Apartment/Suite number cannot exceed 255 characters',
            'house.regex' => 'Apartment/Suite number must contain only English letters, numbers, and basic symbols (. , # & / ( ) -)',
            
            'city.required' => 'City is required',
            'city.min' => 'City must be at least 2 characters long',
            'city.max' => 'City name cannot exceed 255 characters',
            'city.regex' => 'City name must contain only English letters, numbers, and basic symbols (. , # & / ( ) -)',
            
            'state.required' => 'State is required',
            'state.in' => 'Please select a valid US state',
            
            'postal_code.required' => 'ZIP/Postal code is required',
            'postal_code.min' => 'ZIP/Postal code must be at least 5 characters long',
            'postal_code.max' => 'ZIP/Postal code cannot exceed 20 characters',
            'postal_code.regex' => 'ZIP/Postal code can only contain letters, numbers, spaces, and dashes',
            
            'country.required' => 'Country is required',
            'country.in' => 'Currently we only ship to United States',
            
            'payment_method.required' => 'Please select a payment method',
            'payment_method.in' => 'Please select a valid payment method (Zelle or Cryptocurrency)',
            
            'comment.max' => 'Order notes cannot exceed 1000 characters',
            
            'ageVerified.required' => 'Age verification is required',
            'ageVerified.accepted' => 'You must confirm that you are at least 21 years old to continue',
        ];
    }
}
