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

            // Store invoice data in session for verification
            Session::put('payment_invoice_' . $validated['order_id'], [
                'invoice_id' => $invoice['id'],
                'amount' => $validated['amount'],
                'currency' => $validated['currency'],
                'created_at' => now()->toDateTimeString(),
            ]);

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
        Log::info('NOWPayments IPN received', $request->all());

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
            'data' => $data
        ]);

        // Находим заказ
        $order = Order::find($orderId);
        
        if (!$order) {
            Log::error('Order not found for successful payment', ['order_id' => $orderId]);
            return;
        }

        // Обновляем статус заказа
        $order->status = 'Оплачен';
        $order->payment_status = 'completed';
        $order->save();

        // Добавляем запись в историю статусов
        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status' => 'Оплачен',
            'comment' => 'Payment received via cryptocurrency'
        ]);

        // Отправляем email уведомления
        try {
            // Уведомление клиенту
            if ($order->email) {
                Notification::route('mail', $order->email)
                    ->notify(new OrderConfirmation($order));
            }
            
            // Уведомление администратору
            $adminEmail = config('mail.admin_address', 'admin@crusestick.com');
            Notification::route('mail', $adminEmail)
                ->notify(new AdminOrderNotification($order, $adminEmail));
                
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
            'data' => $data
        ]);

        $order = Order::find($orderId);
        
        if ($order) {
            $order->payment_status = 'partial';
            $order->save();
            
            // Добавляем запись в историю статусов
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => 'Частичная оплата',
                'comment' => 'Partial payment received. Amount: ' . ($data['actually_paid'] ?? 'unknown')
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
            'data' => $data
        ]);

        $order = Order::find($orderId);
        
        if ($order) {
            $order->payment_status = 'failed';
            $order->save();
            
            // Добавляем запись в историю статусов
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => 'Платеж не прошел',
                'comment' => 'Payment failed or expired'
            ]);
        }
    }

    /**
     * Payment success page
     */
    public function paymentSuccess(Request $request)
    {
        $orderId = $request->get('order_id');
        $order = null;
        
        if ($orderId) {
            $order = Order::find($orderId);
        }
        
        return view('payment.success', compact('order'));
    }

    /**
     * Payment cancel page
     */
    public function paymentCancel(Request $request)
    {
        $orderId = $request->get('order_id');
        $order = null;
        
        if ($orderId) {
            $order = Order::find($orderId);
            
            // Обновляем статус заказа на "Отменен", если платеж был отменен
            if ($order && $order->payment_status === 'waiting') {
                $order->status = 'Отменен';
                $order->payment_status = 'cancelled';
                $order->save();
                
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status' => 'Отменен',
                    'comment' => 'Payment cancelled by user'
                ]);
            }
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