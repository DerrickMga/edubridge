<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PolicyRiskEvent extends Model
{
    protected $fillable = [
        'code', 'title', 'category', 'severity', 'trigger',
        'response_playbook', 'policy_id', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function policy()    { return $this->belongsTo(TeacherPolicy::class, 'policy_id'); }
    public function incidents() { return $this->hasMany(PolicyRiskIncident::class, 'risk_event_id'); }

    public static function severities(): array { return ['low', 'medium', 'high', 'critical']; }
    public static function categories(): array { return ['conduct', 'attendance', 'ip', 'safeguarding', 'payment', 'legal', 'technical']; }
}
