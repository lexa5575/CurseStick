<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;
    
    /**
     * ИСПРАВЛЕНИЕ: добавлено отсутствующее поле settings
     */
    protected $fillable = [
        'name',
        'code',
        'image_path',
        'background_color',
        'is_active',
        'display_order',
        'settings',
    ];
    
    /**
     * ИСПРАВЛЕНИЕ: добавлен cast для settings
     */
    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
        'settings' => 'array',
    ];
    
    /**
     * Получить полный URL для изображения платежной системы.
     */
    public function getImageUrlAttribute(): string
    {
        if ($this->image_path) {
            return asset($this->image_path);
        }
        
        return asset('images/placeholders/payment/' . $this->code . '.svg');
    }
}