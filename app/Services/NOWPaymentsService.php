<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class NOWPaymentsService
{
    protected $client;
    protected $apiKey;
    protected $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('nowpayments.api_key');
        $this->apiUrl = config('nowpayments.api_url');
        
        $this->client = new Client([
            'base_uri' => $this->apiUrl,
            'headers' => [
                'x-api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'timeout' => 30,
        ]);
    }

    /**
     * Create a new invoice for crypto payment
     *
     * @param array $params
     * @return array|null
     */
    public function createInvoice(array $params)
    {
        try {
            // Validate required parameters
            $requiredParams = ['price_amount', 'price_currency', 'order_id'];
            foreach ($requiredParams as $param) {
                if (!isset($params[$param])) {
                    throw new \InvalidArgumentException("Missing required parameter: {$param}");
                }
            }

            // Prepare the invoice data
            $invoiceData = [
                'price_amount' => $params['price_amount'],
                'price_currency' => strtoupper($params['price_currency']),
                'order_id' => $params['order_id'],
                'order_description' => $params['order_description'] ?? 'Order #' . $params['order_id'],
                'ipn_callback_url' => $params['callback_url'] ?? null,
                'success_url' => $params['success_url'] ?? route('home'),
                'cancel_url' => $params['cancel_url'] ?? route('cart.index'),
            ];

            // Send request to create invoice
            $response = $this->client->post('/v1/invoice', [
                'json' => $invoiceData
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            // Log successful invoice creation
            Log::info('NOWPayments invoice created', [
                'invoice_id' => $result['id'] ?? null,
                'order_id' => $params['order_id'],
                'amount' => $params['price_amount'],
                'currency' => $params['price_currency']
            ]);

            return $result;

        } catch (GuzzleException $e) {
            Log::error('NOWPayments API error', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'order_id' => $params['order_id'] ?? null
            ]);
            
            throw new \Exception('Failed to create payment invoice: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('NOWPayments service error', [
                'error' => $e->getMessage(),
                'order_id' => $params['order_id'] ?? null
            ]);
            
            throw $e;
        }
    }

    /**
     * Get invoice details by ID
     *
     * @param string $invoiceId
     * @return array|null
     */
    public function getInvoice(string $invoiceId)
    {
        try {
            $response = $this->client->get("/v1/invoice/{$invoiceId}");
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('NOWPayments get invoice error', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoiceId
            ]);
            
            return null;
        }
    }

    /**
     * Get available currencies
     *
     * @return array
     */
    public function getAvailableCurrencies()
    {
        try {
            $response = $this->client->get('/v1/currencies');
            $result = json_decode($response->getBody()->getContents(), true);
            
            return $result['currencies'] ?? [];
        } catch (GuzzleException $e) {
            Log::error('NOWPayments get currencies error', [
                'error' => $e->getMessage()
            ]);
            
            return [];
        }
    }
} 