<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FaqController extends Controller
{
    /**
     * Отображение страницы FAQ с вопросами и ответами
     */
    public function index()
    {
        // Загружаем активные FAQ, отсортированные по порядку
        $faqs = Faq::where('is_active', true)
            ->orderBy('order')
            ->get()
            ->map(function ($faq) {
                return [
                    'id' => $faq->id,
                    'question' => $faq->question,
                    'answer' => $faq->answer,
                ];
            });
            
        return Inertia::render('Faq/Index', [
            'faqs' => $faqs,
        ]);
    }
}
