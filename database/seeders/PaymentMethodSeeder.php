<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paymentMethods = [
            [
                'name' => 'Visa',
                'code' => 'visa',
                'background_color' => '#ffffff',
                'display_order' => 1,
            ],
            [
                'name' => 'PayPal',
                'code' => 'paypal',
                'background_color' => '#ffffff',
                'display_order' => 2,
            ],
            [
                'name' => 'Crypto',
                'code' => 'crypto',
                'background_color' => '#ffffff',
                'display_order' => 3,
            ],
            [
                'name' => 'Wise',
                'code' => 'wise',
                'background_color' => '#dbeafe',
                'display_order' => 4,
            ],
            [
                'name' => 'Zelle',
                'code' => 'zelle',
                'background_color' => '#9333ea',
                'display_order' => 5,
            ],
            [
                'name' => 'CashApp',
                'code' => 'cashapp',
                'background_color' => '#22c55e',
                'display_order' => 6,
            ],
        ];
        
        foreach ($paymentMethods as $method) {
            PaymentMethod::updateOrCreate(
                ['code' => $method['code']],
                $method
            );
        }
    }
}
