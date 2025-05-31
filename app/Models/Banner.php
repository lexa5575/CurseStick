<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'image',
        'text',
        'is_active',
        'order',
        'url',
        'button_text',
        'text_color',
        'text_size',
        'text_weight',
        'text_shadow',
        'text_alignment',
        'overlay_color',
        'subtitle',
    ];
    
    /**
     * Добавляем атрибуты, которые должны быть доступны для сериализации JSON
     *
     * @var array
     */
    protected $appends = ['image_url'];
    
    /**
     * Получить URL изображения
     *
     * @return string
     */
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            // Согласно документации Laravel, используем Storage::url
            // Для отладки выводим полный абсолютный URL
            return url(Storage::url($this->image));
        }
        
        return asset('images/banners/default-banner.jpg');
    }
}