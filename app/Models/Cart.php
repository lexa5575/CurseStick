<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

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
        'uuid',
    ];
    
    /**
     * Создаем автоматически UUID при создании корзины
     */
    protected static function booted()
    {
        static::creating(function ($cart) {
            $cart->uuid = $cart->uuid ?? (string) Str::uuid();
        });
    }
    
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
}
