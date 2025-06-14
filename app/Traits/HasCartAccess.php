<?php

namespace App\Traits;

use App\Models\Cart;
use Illuminate\Support\Facades\Auth;

trait HasCartAccess
{
    /**
     * Get user's cart with unified logic
     * 
     * @return Cart|null
     */
    protected function getUserCart(): ?Cart
    {
        $userId = Auth::id();
        $sessionId = session()->getId();

        // For authenticated users, search by user_id
        if ($userId) {
            $cart = Cart::where('user_id', $userId)
                ->where('expires_at', '>', now())
                ->first();
            
            // If cart found but has different session_id, update it to current session
            if ($cart && $cart->session_id !== $sessionId) {
                $cart->session_id = $sessionId;
                $cart->save();
                
                // Update session with cart_id for compatibility
                session(['cart_id' => $cart->id]);
            }
            
            return $cart;
        }

        // For guests, search by session_id
        $cart = Cart::where('session_id', $sessionId)
            ->where('expires_at', '>', now())
            ->first();

        return $cart;
    }
}