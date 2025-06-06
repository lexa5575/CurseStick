<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'total',
        'name',
        'company',
        'street',  // Соответствует полю 'address' в форме
        'house',   // Соответствует полю 'addressUnit' в форме
        'city',
        'state',
        'postal_code', // Соответствует полю 'zipcode' в форме
        'country',
        'phone',
        'email',
        'comment',
        'payment_method',
        'payment_status',
        'payment_invoice_id',
        // tracking_number удалён
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories()
    {
        return $this->hasMany(OrderStatusHistory::class);
    }
}
