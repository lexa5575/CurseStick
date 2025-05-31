<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaymentMethodController extends Controller
{
    /**
     * Возвращает список активных платежных методов.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActivePaymentMethods()
    {
        return PaymentMethod::where('is_active', true)
            ->orderBy('display_order')
            ->get();
    }
    
    /**
     * Загружает изображение для платежного метода.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function uploadImage(Request $request, $id)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        
        $paymentMethod = PaymentMethod::findOrFail($id);
        
        if ($request->hasFile('image')) {
            // Удаляем старое изображение, если оно существует
            if ($paymentMethod->image_path && Storage::exists('public/' . $paymentMethod->image_path)) {
                Storage::delete('public/' . $paymentMethod->image_path);
            }
            
            // Сохраняем новое изображение
            $imagePath = $request->file('image')->store('payment_methods', 'public');
            $paymentMethod->image_path = $imagePath;
            $paymentMethod->save();
            
            return redirect()->back()->with('success', 'Изображение успешно загружено');
        }
        
        return redirect()->back()->with('error', 'Ошибка при загрузке изображения');
    }
}
