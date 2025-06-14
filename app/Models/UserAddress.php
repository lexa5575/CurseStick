<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserAddress extends Model
{
    use HasFactory;

    /**
     * ИСПРАВЛЕНИЕ: убрали user_id из $fillable (небезопасно!)
     * user_id устанавливается программно: auth()->id()
     */
    protected $fillable = [
        'street',
        'house',
        'city',
        'postal_code',
        'country',
        'is_main',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}