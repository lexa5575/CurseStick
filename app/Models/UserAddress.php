<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
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