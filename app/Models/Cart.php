<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class Cart extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'session_id', 
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Получить все элементы корзины
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Получить пользователя, которому принадлежит корзина
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Получить подитог корзины
     */
    public function getSubtotalAttribute()
    {
        return $this->items->sum('total');
    }

    /**
     * Добавить продукт в корзину
     */
    public function addProduct(Product $product, int $quantity = 1, array $options = [])
    {
        $item = $this->items()
            ->where('itemable_type', Product::class)
            ->where('itemable_id', $product->id)
            ->first();

        if ($item) {
            $item->increment('quantity', $quantity);
        } else {
            $this->items()->create([
                'itemable_type' => Product::class,
                'itemable_id' => $product->id,
                'quantity' => $quantity,
                'price' => $product->discount ? ($product->price - $product->discount) : $product->price,
                'options' => $options,
            ]);
        }
    }
}
