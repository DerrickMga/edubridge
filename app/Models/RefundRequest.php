<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefundRequest extends Model
{
    protected $fillable = [
        'user_id', 'payment_id', 'course_id', 'amount', 'currency',
        'status', 'reason', 'admin_notes', 'decided_by', 'decided_at',
    ];
    protected $casts = [
        'amount'      => 'decimal:2',
        'decided_at'  => 'datetime',
    ];

    public function user()     { return $this->belongsTo(User::class); }
    public function payment()  { return $this->belongsTo(Payment::class); }
    public function course()   { return $this->belongsTo(Course::class); }
    public function decider()  { return $this->belongsTo(User::class, 'decided_by'); }

    public static function statuses(): array { return ['pending', 'approved', 'rejected', 'processed']; }
}
