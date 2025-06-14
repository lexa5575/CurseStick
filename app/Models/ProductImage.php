<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'image',
        'order',
    ];
    
    protected $casts = [
        'order' => 'integer',
    ];
    
    protected $appends = ['image_url'];

    /**
     * Получить URL изображения
     */
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return Storage::disk('public')->url($this->image);
        }
        
        return asset('images/placeholders/product-placeholder.jpg');
    }

    /**
     * Связи
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    /**
     * Scopes
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }
}