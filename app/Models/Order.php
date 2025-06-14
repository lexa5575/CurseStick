<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    // Order status constants
    public const STATUS_NEW = 'Новый';
    public const STATUS_PAID = 'Оплачен';
    public const STATUS_PROCESSED = 'Обработан';
    public const STATUS_SHIPPED = 'Отправлен';
    public const STATUS_DELIVERED = 'Доставлен';
    public const STATUS_CANCELLED = 'Отменен';

    // Payment status constants
    public const PAYMENT_PENDING = 'pending';
    public const PAYMENT_COMPLETED = 'completed';
    public const PAYMENT_FAILED = 'failed';
    public const PAYMENT_REFUNDED = 'refunded';

    /**
     * Get all available order statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_NEW => self::STATUS_NEW,
            self::STATUS_PAID => self::STATUS_PAID,
            self::STATUS_PROCESSED => self::STATUS_PROCESSED,
            self::STATUS_SHIPPED => self::STATUS_SHIPPED,
            self::STATUS_DELIVERED => self::STATUS_DELIVERED,
            self::STATUS_CANCELLED => self::STATUS_CANCELLED,
        ];
    }

    /**
     * Get all available payment statuses
     */
    public static function getPaymentStatuses(): array
    {
        return [
            self::PAYMENT_PENDING => 'Ожидает оплаты',
            'processing' => 'Обрабатывается',
            self::PAYMENT_COMPLETED => 'Оплачен',
            'cancelled' => 'Отменен',
            self::PAYMENT_FAILED => 'Ошибка оплаты',
            self::PAYMENT_REFUNDED => 'Возвращен',
        ];
    }

    /**
     * Get status color for badges
     */
    public static function getStatusColor(string $status): string
    {
        return match ($status) {
            self::STATUS_NEW => 'info',
            self::STATUS_PAID => 'success',
            self::STATUS_PROCESSED => 'warning',
            self::STATUS_SHIPPED => 'primary',
            self::STATUS_DELIVERED => 'success',
            self::STATUS_CANCELLED => 'danger',
            default => 'gray',
        };
    }

    /**
     * Safe fields for mass assignment
     */
    protected $fillable = [
        'status',
        'name',
        'company',
        'street',
        'house',
        'city', 
        'state',
        'postal_code',
        'country',
        'phone',
        'email',
        'comment',
        'payment_method',
        'payment_status', // Added for admin panel editing
        // System fields excluded from mass assignment for security:
        // user_id, total, payment_invoice_id, tracking_number
    ];
    
    /**
     * Fields for type casting
     */
    protected $casts = [
        'total' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'user_id' => 'integer',
    ];
    
    /**
     * Fields for eager loading
     * Note: Removed automatic eager loading to prevent N+1 queries
     * Use explicit ->with(['items', 'items.product']) when needed
     */
    // protected $with = ['items', 'items.product'];

    /**
     * Model events
     */
    protected static function booted()
    {
        static::creating(function ($order) {
            // Generate unique order number
            $order->order_number = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
            
            // Generate secure token for payment URLs
            $order->payment_token = \Illuminate\Support\Str::random(64);
        });
        
        static::updated(function ($order) {
            // Log status changes
            if ($order->isDirty('status')) {
                $order->statusHistories()->create([
                    'status' => $order->status,
                    'created_by' => auth()->id(),
                    'comment' => 'Status changed to: ' . $order->status,
                ]);
            }
        });
    }

    /**
     * Relationships
     */
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
    
    /**
     * Computed attributes
     */
    public function getTotalAmountAttribute()
    {
        return $this->items->sum(function ($item) {
            return ($item->price - $item->discount) * $item->quantity;
        });
    }
    
    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->whereIn('payment_status', ['completed', 'paid']);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
    
    /**
     * Methods
     */
    public function isPaid(): bool
    {
        return in_array($this->payment_status, ['completed', 'paid']);
    }
    
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'processing']);
    }
}