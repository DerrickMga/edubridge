<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'description', 'type', 'value', 'currency', 'course_id',
        'max_redemptions', 'per_user_limit', 'min_order_value',
        'starts_at', 'expires_at', 'is_active', 'created_by',
    ];
    protected $casts = [
        'value'            => 'decimal:2',
        'min_order_value'  => 'decimal:2',
        'starts_at'        => 'datetime',
        'expires_at'       => 'datetime',
        'is_active'        => 'boolean',
        'per_user_limit'   => 'integer',
        'max_redemptions'  => 'integer',
    ];

    public function course()      { return $this->belongsTo(Course::class); }
    public function redemptions() { return $this->hasMany(CouponRedemption::class); }
    public function creator()     { return $this->belongsTo(User::class, 'created_by'); }

    public function isValidNow(): bool
    {
        if (! $this->is_active) return false;
        $now = now();
        if ($this->starts_at && $now->lt($this->starts_at))   return false;
        if ($this->expires_at && $now->gt($this->expires_at)) return false;
        if ($this->max_redemptions && $this->redemptions()->count() >= $this->max_redemptions) return false;
        return true;
    }

    public function appliesToCourse(?int $courseId): bool
    {
        return $this->course_id === null || (int) $this->course_id === (int) $courseId;
    }

    public function discountFor(float $subtotal, string $currency = 'USD'): float
    {
        if ($this->min_order_value && $subtotal < (float) $this->min_order_value) return 0.0;
        if ($this->type === 'percent') {
            return round($subtotal * ((float) $this->value / 100), 2);
        }
        if (strtoupper($this->currency) !== strtoupper($currency)) return 0.0;
        return min($subtotal, (float) $this->value);
    }

    public function timesUsedBy(int $userId): int
    {
        return $this->redemptions()->where('user_id', $userId)->count();
    }
}
