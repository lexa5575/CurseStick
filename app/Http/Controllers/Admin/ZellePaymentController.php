<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ZellePaymentConfirmationMail;
use App\Models\ZelleAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\JsonResponse;

class ZellePaymentController extends Controller
{
    /**
     * Send payment details email to the customer
     */
    public function sendPaymentDetails(ZelleAddress $zelleAddress, ?float $totalAmount = null): JsonResponse
    {
        try {
            // Получаем последний заказ клиента для определения суммы, если не передана
            if (!$totalAmount) {
                $latestOrder = $zelleAddress->orders()->latest()->first();
                $totalAmount = $latestOrder ? $latestOrder->total : null;
            }
            
            // Отправляем email с платежными реквизитами
            Mail::to($zelleAddress->email)->send(new ZellePaymentConfirmationMail($zelleAddress, $totalAmount));
            
            return response()->json([
                'success' => true,
                'message' => 'Payment details email sent successfully to ' . $zelleAddress->email
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Failed to send Zelle payment details email', [
                'zelle_address_id' => $zelleAddress->id,
                'email' => $zelleAddress->email,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Send payment details via route (for web interface)
     */
    public function sendPaymentDetailsRoute(Request $request, ZelleAddress $zelleAddress)
    {
        $response = $this->sendPaymentDetails($zelleAddress);
        
        if ($response->getData()->success) {
            return redirect()->back()->with('success', 'Payment details sent successfully to ' . $zelleAddress->email);
        } else {
            return redirect()->back()->with('error', 'Failed to send payment details: ' . $response->getData()->message);
        }
    }
}