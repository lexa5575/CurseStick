<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderStatusHistory extends Model
{
    use HasFactory;

    /**
     * ИСПРАВЛЕНИЕ: добавлены поля из обновленной миграции
     * created_by НЕ в $fillable - устанавливается программно: auth()->id()
     */
    protected $fillable = [
        'order_id',
        'status',
        'comment',
    ];

    /**
     * ИСПРАВЛЕНИЕ: включены timestamps (убрано $timestamps = false)
     * Миграция содержит timestamps
     */

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Пользователь который изменил статус
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}