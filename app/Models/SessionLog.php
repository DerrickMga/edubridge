<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionLog extends Model
{
    protected $fillable = [
        'live_session_id', 'teacher_id',
        'actual_duration_minutes', 'actual_student_count',
        'notes', 'submitted_at',
    ];

    protected $casts = ['submitted_at' => 'datetime'];

    public function session()     { return $this->belongsTo(LiveSession::class, 'live_session_id'); }
    public function teacher()     { return $this->belongsTo(User::class, 'teacher_id'); }
    public function paymentItem() { return $this->hasOne(TeacherPaymentItem::class); }
}
