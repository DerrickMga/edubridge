<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherAvailability extends Model
{
    protected $table = 'teacher_availability';

    protected $fillable = ['teacher_id', 'day_of_week', 'start_time', 'end_time'];

    public function teacher() { return $this->belongsTo(User::class, 'teacher_id'); }

    public static function dayName(int $dow): string
    {
        return ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'][$dow] ?? '';
    }
}
