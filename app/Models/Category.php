<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image',
    ];
    
    /**
     * Добавляем атрибуты, которые должны быть доступны для сериализации JSON
     *
     * @var array
     */
    protected $appends = ['image_url'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
    
    /**
     * Получить URL изображения
     *
     * @return string
     */
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return Storage::disk('public')->url($this->image);
        }
        
        return asset('images/placeholders/category-placeholder.jpg');
    }
}
