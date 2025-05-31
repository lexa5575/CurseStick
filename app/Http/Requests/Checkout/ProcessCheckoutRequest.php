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
        // Регулярное выражение для проверки английских символов, цифр и базовых спецсимволов
        $englishRegex = '/^[A-Za-z0-9\s\-\.\,\#\&\/\(\)]*$/'; 
        
        return [
            'name' => ['required', 'string', 'max:255', 'regex:' . $englishRegex],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'company' => ['nullable', 'string', 'max:255', 'regex:' . $englishRegex],
            'street' => ['required', 'string', 'max:500', 'regex:' . $englishRegex], // Переименовано из 'address'
            'house' => ['nullable', 'string', 'max:255', 'regex:' . $englishRegex],  // Переименовано из 'addressUnit'
            'city' => ['required', 'string', 'max:255', 'regex:' . $englishRegex],
            'state' => ['required', 'string', 'max:255', 'regex:' . $englishRegex], // Сохранено как required
            'postal_code' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9\-\s]*$/'], // Переименовано из 'zipcode'
            'country' => ['required', 'string', 'max:100'],
            'payment_method' => ['required', 'string', 'in:cash,card,online,zelle,crypto'], // Изменено с nullable на required, чтобы соответствовать клиентской валидации
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }
    
    /**
     * Настройка сообщений об ошибках валидации
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // Required field messages
            'name.required' => 'Name is required',
            'email.required' => 'Email is required',
            'email.email' => 'Invalid email format',
            'street.required' => 'Address is required',
            'city.required' => 'City is required',
            'state.required' => 'State is required',
            'postal_code.required' => 'Zipcode is required',
            'country.required' => 'Country is required',
            
            // English language validation messages
            'name.regex' => 'Name must contain only English letters and numbers',
            'company.regex' => 'Company name must contain only English letters and numbers',
            'street.regex' => 'Address must contain only English letters, numbers, and basic symbols',
            'house.regex' => 'Apartment/Suite number must contain only English letters and numbers',
            'city.regex' => 'City must contain only English letters',
            'state.regex' => 'State must contain only English letters',
            'postal_code.regex' => 'Zipcode must contain only English letters and numbers',
        ];
    }
}
