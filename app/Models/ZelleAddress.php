<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZelleAddress extends Model
{
    use HasFactory;

    /**
     * Атрибуты, которые можно массово назначать.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'address',
        'note',
    ];

    /**
     * Получить заказы, связанные с этим Zelle адресом.
     * Связь по email клиента.
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'email', 'email');
    }
}
