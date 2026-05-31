<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherContract extends Model
{
    protected $fillable = [
        'teacher_id', 'version', 'status',
        'issued_at', 'effective_at', 'expires_at',
        'rate_usd', 'payment_terms', 'term_months', 'exclusivity',
        'terms_snapshot', 'addendum',
        'signed_at', 'signed_name', 'signed_ip', 'signed_user_agent',
        'terminated_at', 'terminated_reason', 'issued_by',
    ];

    protected $casts = [
        'issued_at'      => 'datetime',
        'effective_at'   => 'datetime',
        'expires_at'     => 'datetime',
        'signed_at'      => 'datetime',
        'terminated_at'  => 'datetime',
        'rate_usd'       => 'decimal:2',
        'terms_snapshot' => 'array',
    ];

    public function teacher() { return $this->belongsTo(User::class, 'teacher_id'); }
    public function issuer()  { return $this->belongsTo(User::class, 'issued_by'); }

    public function isSigned(): bool      { return $this->status === 'signed'; }
    public function isActive(): bool      { return $this->status === 'signed' && ! $this->isExpired(); }
    public function isExpired(): bool     { return $this->expires_at !== null && $this->expires_at->isPast(); }
}
