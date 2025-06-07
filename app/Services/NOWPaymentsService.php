<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
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
        
        // Проверяем наличие API ключа
        if (empty($this->apiKey)) {
            throw new \Exception('NOWPayments API key is not configured. Please add NOWPAYMENTS_API_KEY to your .env file.');
        }
        
        $this->client = new Client([
            'base_uri' => $this->apiUrl,
            'headers' => [
                'x-api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'timeout' => 30,
            'verify' => config('app.env') === 'production' ? true : false,
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
                'price_amount' => (string)$params['price_amount'],
                'price_currency' => strtoupper($params['price_currency']),
                'order_id' => (string)$params['order_id'],
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

            return $result;

        } catch (RequestException $e) {
            $errorMessage = 'NOWPayments API error';
            $errorDetails = [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'order_id' => $params['order_id'] ?? null
            ];
            
            // Пытаемся получить детали ошибки из ответа
            if ($e->hasResponse() && $response = $e->getResponse()) {
                $body = $response->getBody()->getContents();
                $errorDetails['response_body'] = $body;
                $errorDetails['status_code'] = $response->getStatusCode();
                
                // Пытаемся декодировать JSON ответ
                $jsonError = json_decode($body, true);
                if ($jsonError && isset($jsonError['message'])) {
                    $errorMessage .= ': ' . $jsonError['message'];
                }
            }
            
            Log::error('NOWPayments API error', $errorDetails);
            
            throw new \Exception($errorMessage);
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
        } catch (RequestException $e) {
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
        } catch (RequestException $e) {
            Log::error('NOWPayments get currencies error', [
                'error' => $e->getMessage()
            ]);
            
            return [];
        }
    }
} 