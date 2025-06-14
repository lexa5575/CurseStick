<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

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
        // System fields excluded from mass assignment for security:
        // user_id, total, payment_status, payment_invoice_id, tracking_number
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