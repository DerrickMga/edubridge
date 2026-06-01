<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = ['user_id', 'plan_id', 'status', 'started_at', 'expires_at', 'canceled_at', 'payment_id'];
    protected $casts = ['started_at' => 'datetime', 'expires_at' => 'datetime', 'canceled_at' => 'datetime'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function plan(): BelongsTo { return $this->belongsTo(SubscriptionPlan::class, 'plan_id'); }
    public function payment(): BelongsTo { return $this->belongsTo(Payment::class); }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->expires_at && $this->expires_at->isFuture();
    }
}
