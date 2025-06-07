<?php

namespace App\Http\Controllers;

use App\Mail\ContactFormMail;
use App\Models\ContactFormLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class ContactController extends Controller
{
    public function sendMessage(Request $request)
    {
        // Проверка honeypot поля (если заполнено - это бот)
        if ($request->filled('website')) {
            // Логируем как спам
            ContactFormLog::create([
                'name' => $request->input('name', 'Bot'),
                'email' => $request->input('email', 'bot@spam.com'),
                'title' => $request->input('title', ''),
                'message' => $request->input('question_topic', 'Honeypot triggered'),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'is_spam' => true,
                'email_sent' => false,
            ]);
            
            // Просто перенаправляем обратно с "успешным" сообщением, чтобы не раскрывать защиту
            return back()->with('success', 'Thank you for your message! We will respond to you within 24-48 hours.');
        }
        
        // Rate limiting - максимум 3 сообщения за 10 минут с одного IP
        $key = 'contact-form:' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            
            return back()->with('error', 'Too many messages sent. Please try again in ' . ceil($seconds / 60) . ' minutes.')
                         ->withInput();
        }
        
        RateLimiter::hit($key, 600); // 600 секунд = 10 минут

        // Валидация данных
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'title' => 'nullable|string|max:255',
            'question_topic' => 'required|string|min:20|max:1200',
        ]);
        
        // Добавляем IP адрес для отслеживания
        $validated['ip_address'] = $request->ip();
        $validated['user_agent'] = $request->userAgent();
        
        // Создаем запись в логе
        $log = ContactFormLog::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'title' => $validated['title'] ?? null,
            'message' => $validated['question_topic'],
            'ip_address' => $validated['ip_address'],
            'user_agent' => $validated['user_agent'],
            'is_spam' => false,
            'email_sent' => false,
        ]);

        try {
            // Получаем email администратора из конфигурации
            $adminEmail = config('mail.admin_address', 'admin@crusestick.com');
            
            // Отправляем email
            Mail::to($adminEmail)->send(new ContactFormMail($validated));
            
            // Обновляем статус отправки
            $log->update(['email_sent' => true]);
            
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