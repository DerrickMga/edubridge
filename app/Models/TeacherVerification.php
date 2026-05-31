<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherVerification extends Model
{
    protected $fillable = [
        'teacher_id', 'status',
        'full_legal_name', 'national_id_number',
        'id_document_front', 'id_document_back',
        'proof_of_qualification', 'selfie_with_id',
        'bank_name', 'bank_account_number', 'bank_branch_code',
        'ecocash_number', 'innbucks_number', 'paynow_email',
        'admin_notes', 'reviewed_by', 'submitted_at', 'reviewed_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at'  => 'datetime',
    ];

    const STATUS_PENDING     = 'pending';
    const STATUS_APPROVED    = 'approved';
    const STATUS_REJECTED    = 'rejected';
    const STATUS_RESUBMIT    = 'needs_resubmission';

    public function teacher()  { return $this->belongsTo(User::class, 'teacher_id'); }
    public function reviewer() { return $this->belongsTo(User::class, 'reviewed_by'); }

    public function isPending(): bool   { return $this->status === self::STATUS_PENDING; }
    public function isApproved(): bool  { return $this->status === self::STATUS_APPROVED; }
    public function isRejected(): bool  { return $this->status === self::STATUS_REJECTED; }

    public function statusBadgeClass(): string
    {
        return match($this->status) {
            'approved'           => 'badge-green',
            'rejected'           => 'badge-red',
            'needs_resubmission' => 'badge-amber',
            default              => 'badge-slate',
        };
    }
}
