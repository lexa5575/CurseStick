<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Favorite extends Model
{
    use HasFactory;

    /**
     * ИСПРАВЛЕНИЕ: убрали user_id из $fillable (небезопасно!)
     * user_id устанавливается программно: auth()->id()
     */
    protected $fillable = [
        'product_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}