<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CartItem extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'cart_id',
        'itemable_id',
        'itemable_type',
        'quantity',
        'price',
        'options',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'options' => 'array',
        'price' => 'decimal:2',
    ];
    
    /**
     * Получить корзину, к которой принадлежит элемент
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }
    
    /**
     * Получить элемент, который добавлен в корзину (полиморфная связь)
     */
    public function itemable(): MorphTo
    {
        return $this->morphTo();
    }
    
    /**
     * Получить общую стоимость элемента корзины
     */
    public function getTotalAttribute(): float
    {
        return $this->price * $this->quantity;
    }
}
