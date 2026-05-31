<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherShift extends Model
{
    protected $fillable = [
        'teacher_id', 'course_id', 'live_session_id', 'title',
        'starts_at', 'ends_at', 'status', 'auto_assigned',
        'hourly_rate_usd_snapshot', 'hours_worked', 'payout_amount_usd',
        'assigned_by', 'notes',
    ];

    protected $casts = [
        'starts_at'                 => 'datetime',
        'ends_at'                   => 'datetime',
        'auto_assigned'             => 'boolean',
        'hourly_rate_usd_snapshot'  => 'decimal:2',
        'hours_worked'              => 'decimal:2',
        'payout_amount_usd'         => 'decimal:2',
    ];

    public function teacher()     { return $this->belongsTo(User::class, 'teacher_id'); }
    public function course()      { return $this->belongsTo(Course::class); }
    public function liveSession() { return $this->belongsTo(LiveSession::class); }
    public function assigner()    { return $this->belongsTo(User::class, 'assigned_by'); }

    public function getDurationHoursAttribute(): float
    {
        return round($this->starts_at->diffInMinutes($this->ends_at) / 60, 2);
    }

    public function computePayout(): void
    {
        $rate = $this->hourly_rate_usd_snapshot ?? $this->teacher?->hourly_rate_usd ?? 0;
        $hours = $this->hours_worked ?? $this->duration_hours;
        $this->payout_amount_usd = round($hours * (float) $rate, 2);
    }
}
