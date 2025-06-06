<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\View\View;

class FaqController extends Controller
{
    /**
     * Отображение страницы FAQ с вопросами и ответами
     */
    public function index(): View
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
            
        return view('faq.index', [
            'faqs' => $faqs,
        ]);
    }
}
