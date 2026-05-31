<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PolicyRiskIncident extends Model
{
    protected $fillable = [
        'risk_event_id', 'teacher_id', 'reported_by',
        'status', 'severity_override', 'summary',
        'resolution_notes', 'evidence', 'opened_at', 'closed_at',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'evidence'  => 'array',
    ];

    public function event()    { return $this->belongsTo(PolicyRiskEvent::class, 'risk_event_id'); }
    public function teacher()  { return $this->belongsTo(User::class, 'teacher_id'); }
    public function reporter() { return $this->belongsTo(User::class, 'reported_by'); }

    public static function statuses(): array { return ['open', 'investigating', 'resolved', 'escalated', 'dismissed']; }
}
