# NOWPayments Configuration

To integrate NOWPayments crypto payment gateway, add the following environment variables to your `.env` file:

```
# NOWPayments API Configuration
NOWPAYMENTS_API_KEY=your_api_key_here
NOWPAYMENTS_API_URL=https://api.nowpayments.io
NOWPAYMENTS_IPN_SECRET=your_ipn_secret_here
NOWPAYMENTS_SANDBOX=false
```

## Getting Your API Credentials

1. Sign up for a NOWPayments account at https://nowpayments.io/
2. Go to your dashboard and navigate to API settings
3. Generate your API key
4. Set up your IPN secret for secure webhook callbacks

## Testing in Sandbox Mode

For testing, you can use the sandbox environment:
```
NOWPAYMENTS_API_URL=https://api.sandbox.nowpayments.io
NOWPAYMENTS_SANDBOX=true
```

## Usage Example

```php
// In your controller or checkout process
$payment = app(PaymentController::class)->createCryptoPayment(new Request([
    'amount' => 100.00,
    'currency' => 'USD',
    'order_id' => 'ORDER-123',
    'order_description' => 'Purchase from CruseStick Store',
    'callback_url' => route('payment.ipn'),
    'success_url' => route('payment.success'),
    'cancel_url' => route('payment.cancel'),
]));
``` 