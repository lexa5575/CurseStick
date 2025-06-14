<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Coupon extends Model
{
    use HasFactory;

    /**
     * Safe fields for mass assignment
     */
    protected $fillable = [
        'code',
        'name',
        'description',
        'discount_type',
        'discount_value',
        'is_active',
        'valid_from',
        'valid_until',
        'usage_limit',
        'applies_to_all_categories',
        // usage_count excluded for security - only set programmatically
    ];

    /**
     * Fields for type casting
     */
    protected $casts = [
        'discount_value' => 'decimal:2',
        'is_active' => 'boolean',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'usage_limit' => 'integer',
        'usage_count' => 'integer',
        'applies_to_all_categories' => 'boolean',
    ];

    /**
     * Model events
     */
    protected static function booted()
    {
        // Automatically convert code to uppercase
        static::creating(function ($coupon) {
            $coupon->code = strtoupper($coupon->code);
        });
        
        static::updating(function ($coupon) {
            $coupon->code = strtoupper($coupon->code);
        });
    }

    /**
     * Relationships
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'coupon_categories');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeValid($query)
    {
        $now = Carbon::now();
        return $query->where(function ($q) use ($now) {
            $q->where(function ($subQ) use ($now) {
                // Check if valid_from is null OR valid_from <= now
                $subQ->whereNull('valid_from')
                     ->orWhere('valid_from', '<=', $now);
            })
            ->where(function ($subQ) use ($now) {
                // Check if valid_until is null OR valid_until >= now
                $subQ->whereNull('valid_until')
                     ->orWhere('valid_until', '>=', $now);
            });
        });
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('valid_until')
              ->orWhere('valid_until', '>=', Carbon::now());
        });
    }

    public function scopeUsable($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('usage_limit')
              ->orWhereRaw('usage_count < usage_limit');
        });
    }

    /**
     * Methods
     */
    public function isValid(): bool
    {
        $now = Carbon::now();
        
        // Check if active
        if (!$this->is_active) {
            return false;
        }
        
        // Check valid_from
        if ($this->valid_from && $this->valid_from->gt($now)) {
            return false;
        }
        
        // Check valid_until
        if ($this->valid_until && $this->valid_until->lt($now)) {
            return false;
        }
        
        // Check usage limit
        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) {
            return false;
        }
        
        return true;
    }

    public function isExpired(): bool
    {
        return $this->valid_until && $this->valid_until->lt(Carbon::now());
    }

    public function isPermanent(): bool
    {
        return is_null($this->valid_until);
    }

    public function getFormattedDiscountAttribute(): string
    {
        if ($this->discount_type === 'fixed') {
            return '$' . number_format($this->discount_value, 2);
        }
        return $this->discount_value . '%';
    }

    public function getStatusBadgeAttribute(): string
    {
        if (!$this->is_active) {
            return 'inactive';
        }
        
        if ($this->isExpired()) {
            return 'expired';
        }
        
        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) {
            return 'exhausted';
        }
        
        return 'active';
    }

    public function canBeUsed(): bool
    {
        return $this->isValid();
    }

    /**
     * Safely increment usage count with race condition protection
     */
    public function incrementUsage(): bool
    {
        // Use atomic increment with usage limit check
        if ($this->usage_limit) {
            // Lock the row and check usage limit
            return \DB::transaction(function () {
                $coupon = self::lockForUpdate()->find($this->id);
                
                if ($coupon->usage_count >= $coupon->usage_limit) {
                    return false; // Usage limit exceeded
                }
                
                $coupon->increment('usage_count');
                return true;
            });
        } else {
            // No usage limit - simple increment
            $this->increment('usage_count');
            return true;
        }
    }
}