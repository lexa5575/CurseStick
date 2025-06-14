<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Models\Cart; 

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
        'price', // ДОБАВЛЕНО
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
        'quantity' => 'integer',
    ];
    
    /**
     * Validation rules
     */
    public static $rules = [
        'quantity' => 'required|integer|min:1|max:100',
        'price' => 'required|numeric|min:0.01|max:999999.99',
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