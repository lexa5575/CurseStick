<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItem extends Model
{
    use HasFactory;

    /**
     * Безопасные поля для mass assignment
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
        'discount',
        'product_name',
        'product_sku',
    ];
    
    /**
     * Поля для приведения типов
     */
    protected $casts = [
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'quantity' => 'integer',
    ];

    /**
     * Связи
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    /**
     * Вычисляемые атрибуты
     */
    public function getTotalAttribute()
    {
        return $this->price * $this->quantity;
    }
    
    public function getDiscountedPriceAttribute()
    {
        return $this->price - $this->discount;
    }
    
    public function getSubtotalAttribute()
    {
        return $this->discounted_price * $this->quantity;
    }
    
    /**
     * Методы
     */
    public function fillFromProduct(Product $product)
    {
        $this->price = $product->price;
        $this->discount = $product->discount ?? 0;
        $this->product_name = $product->name;
        $this->product_sku = $product->sku ?? null;
        
        return $this;
    }
}