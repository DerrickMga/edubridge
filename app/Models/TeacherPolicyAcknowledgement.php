<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherPolicyAcknowledgement extends Model
{
    protected $table = 'teacher_policy_acknowledgements';

    protected $fillable = [
        'teacher_id', 'policy_id', 'version',
        'acknowledged_at', 'ip_address', 'user_agent',
    ];

    protected $casts = [
        'acknowledged_at' => 'datetime',
    ];

    public function teacher() { return $this->belongsTo(User::class, 'teacher_id'); }
    public function policy()  { return $this->belongsTo(TeacherPolicy::class, 'policy_id'); }
}
