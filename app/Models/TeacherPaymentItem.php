<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherPaymentItem extends Model
{
    protected $fillable = [
        'teacher_id', 'live_session_id', 'session_log_id',
        'hours_logged', 'student_count', 'hourly_rate_usd', 'total_usd',
        'status', 'admin_notes', 'approved_by', 'approved_at', 'paid_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'paid_at'     => 'datetime',
    ];

    public function teacher()    { return $this->belongsTo(User::class, 'teacher_id'); }
    public function session()    { return $this->belongsTo(LiveSession::class, 'live_session_id'); }
    public function sessionLog() { return $this->belongsTo(SessionLog::class); }
    public function approver()   { return $this->belongsTo(User::class, 'approved_by'); }
}
