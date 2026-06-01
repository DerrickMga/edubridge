<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralCredit extends Model
{
    protected $fillable = ['referrer_id', 'referred_id', 'payment_id', 'amount', 'currency', 'status'];
    protected $casts = ['amount' => 'decimal:2'];

    public function referrer(): BelongsTo { return $this->belongsTo(User::class, 'referrer_id'); }
    public function referred(): BelongsTo { return $this->belongsTo(User::class, 'referred_id'); }
    public function payment(): BelongsTo { return $this->belongsTo(Payment::class); }
}
