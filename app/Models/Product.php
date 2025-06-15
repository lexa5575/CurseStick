<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use App\Traits\HasSlug;

class Product extends Model
{
    use HasFactory, SoftDeletes, HasSlug;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'is_active',
        'is_featured',
        'discount',
        'image',
        'category_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
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

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function cartItems()
    {
        return $this->morphMany(CartItem::class, 'itemable');
    }

    /**
     * Get the URL for the product image.
     */
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return Storage::disk('public')->url($this->image);
        }

        return asset('images/placeholders/product-placeholder.jpg');
    }
    /**
     * Scopes для фильтрации
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeWithDiscount($query)
    {
        return $query->where('discount', '>', 0);
    }

    /**
     * Model events for cleanup
     */
    protected static function booted()
    {
        // When product is soft deleted, remove from carts
        static::deleted(function ($product) {
            if ($product->isForceDeleting()) {
                // Force delete - remove everything
                $product->cartItems()->delete();
                $product->favorites()->delete();
            } else {
                // Soft delete - only remove from carts (keep favorites for restore)
                $product->cartItems()->delete();
            }
        });

        // When product is restored, nothing special needed
        static::restored(function ($product) {
            // Product is back, cart items will be recreated naturally
        });
    }
}
