<?php

namespace App\Http\Controllers;

use App\Mail\ContactFormMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function sendMessage(Request $request)
    {
        // Валидация данных
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'title' => 'nullable|string|max:255',
            'question_topic' => 'required|string|min:20|max:1200',
        ]);

        try {
            // Получаем email администратора из конфигурации
            $adminEmail = config('mail.admin_address', 'admin@crusestick.com');
            
            // Отправляем email
            Mail::to($adminEmail)->send(new ContactFormMail($validated));
            
            // Возвращаемся с успешным сообщением
            return back()->with('success', 'Thank you for your message! We will respond to you within 24-48 hours.');
            
        } catch (\Exception $e) {
            // Логируем ошибку
            \Log::error('Contact form error: ' . $e->getMessage());
            
            // Возвращаемся с ошибкой
            return back()->with('error', 'Sorry, there was an error sending your message. Please try again later.')
                         ->withInput();
        }
    }
} 