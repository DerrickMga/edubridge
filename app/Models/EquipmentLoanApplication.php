<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EquipmentLoanApplication extends Model
{
    protected $fillable = [
        'teacher_id',
        'items_requested',
        'purpose',
        'amount_requested_usd',
        'repayment_period_months',
        'employment_context',
        'teacher_notes',
        'status',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
        'approved_amount_usd',
        'approved_months',
        'monthly_repayment_usd',
        'interest_rate_percent',
        'reference_number',
        'disbursed_at',
        'repayment_starts_on',
        'repayment_ends_on',
    ];

    protected $casts = [
        'items_requested'       => 'array',
        'reviewed_at'           => 'datetime',
        'disbursed_at'          => 'datetime',
        'repayment_starts_on'   => 'date',
        'repayment_ends_on'     => 'date',
        'amount_requested_usd'  => 'decimal:2',
        'approved_amount_usd'   => 'decimal:2',
        'monthly_repayment_usd' => 'decimal:2',
        'interest_rate_percent' => 'decimal:2',
    ];

    /**
     * All loanable equipment items with labels and estimated USD value.
     */
    public static function availableItems(): array
    {
        return [
            'laptop'          => ['label' => 'Laptop / Computer',               'est_usd' => 350],
            'webcam'          => ['label' => 'External HD Webcam',               'est_usd' => 45],
            'ring_light'      => ['label' => 'Ring Light / Lighting Kit',        'est_usd' => 35],
            'headset'         => ['label' => 'Noise-Canceling Headset',          'est_usd' => 60],
            'router'          => ['label' => 'WiFi Router / Network Upgrade',    'est_usd' => 50],
            'data_bundle'     => ['label' => 'Mobile Data Bundle (3 months)',    'est_usd' => 30],
            'ups'             => ['label' => 'UPS / Power Backup Unit',          'est_usd' => 80],
            'desk_chair'      => ['label' => 'Desk & Chair Setup',               'est_usd' => 120],
        ];
    }

    /**
     * Generate a unique reference number on creation.
     */
    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (! $model->reference_number) {
                $model->reference_number = 'EQL-' . strtoupper(Str::random(8));
            }
        });
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'approved', 'disbursed'  => 'badge-green',
            'under_review'           => 'badge-blue',
            'repaying'               => 'badge-purple',
            'completed'              => 'badge-slate',
            'rejected'               => 'badge-red',
            default                  => 'badge-amber',  // pending
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending'      => 'Pending Review',
            'under_review' => 'Under Review',
            'approved'     => 'Approved',
            'rejected'     => 'Rejected',
            'disbursed'    => 'Disbursed',
            'repaying'     => 'Repaying',
            'completed'    => 'Completed',
            default        => ucfirst($this->status),
        };
    }

    public function requestedItemLabels(): array
    {
        $items = static::availableItems();
        return collect($this->items_requested ?? [])
            ->map(fn ($key) => $items[$key]['label'] ?? $key)
            ->toArray();
    }

    public function teacher()  { return $this->belongsTo(User::class, 'teacher_id'); }
    public function reviewer() { return $this->belongsTo(User::class, 'reviewed_by'); }
}
