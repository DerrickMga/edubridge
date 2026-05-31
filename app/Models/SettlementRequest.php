<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettlementRequest extends Model
{
    protected $fillable = [
        'teacher_id', 'amount_usd', 'payment_method',
        'payout_details', 'status', 'reference_number',
        'teacher_notes', 'admin_notes',
        'processed_by', 'processed_at',
    ];

    protected $casts = [
        'payout_details' => 'array',
        'processed_at'   => 'datetime',
        'amount_usd'     => 'decimal:2',
    ];

    const STATUS_PENDING    = 'pending';
    const STATUS_APPROVED   = 'approved';
    const STATUS_PROCESSING = 'processing';
    const STATUS_PAID       = 'paid';
    const STATUS_REJECTED   = 'rejected';

    public function teacher()   { return $this->belongsTo(User::class, 'teacher_id'); }
    public function processor() { return $this->belongsTo(User::class, 'processed_by'); }

    public function statusBadgeClass(): string
    {
        return match($this->status) {
            'paid'       => 'badge-green',
            'approved'   => 'badge-blue',
            'processing' => 'badge-purple',
            'rejected'   => 'badge-red',
            default      => 'badge-slate',
        };
    }

    public function statusLabel(): string
    {
        return match($this->status) {
            'paid'       => 'Paid',
            'approved'   => 'Approved',
            'processing' => 'Processing',
            'rejected'   => 'Rejected',
            default      => 'Pending',
        };
    }
}
