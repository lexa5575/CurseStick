<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use App\Traits\HasSlug;

class Category extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
    ];
    
    protected $appends = ['image_url'];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * ИСПРАВЛЕНИЕ: убран дублирующий код booted() - теперь в HasSlug trait
     */

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function coupons()
    {
        return $this->belongsToMany(Coupon::class, 'coupon_categories');
    }
    
    /**
     * Получить URL изображения
     */
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return Storage::disk('public')->url($this->image);
        }
        
        return asset('images/placeholders/category-placeholder.jpg');
    }
}