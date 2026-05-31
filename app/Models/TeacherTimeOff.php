<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherTimeOff extends Model
{
    protected $table = 'teacher_time_off';

    protected $fillable = ['teacher_id', 'starts_at', 'ends_at', 'reason', 'status', 'approved_by'];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
    ];

    public function teacher()  { return $this->belongsTo(User::class, 'teacher_id'); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }
}
