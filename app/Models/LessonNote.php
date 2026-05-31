<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonNote extends Model
{
    protected $fillable = ['user_id', 'lesson_id', 'course_id', 'timestamp_seconds', 'body'];
    protected $casts = ['timestamp_seconds' => 'integer'];

    public function user()   { return $this->belongsTo(User::class); }
    public function lesson() { return $this->belongsTo(Lesson::class); }
    public function course() { return $this->belongsTo(Course::class); }

    public function getFormattedTimestampAttribute(): ?string
    {
        if ($this->timestamp_seconds === null) return null;
        $s = (int) $this->timestamp_seconds;
        $h = intdiv($s, 3600);
        $m = intdiv($s % 3600, 60);
        $sec = $s % 60;
        return $h > 0
            ? sprintf('%d:%02d:%02d', $h, $m, $sec)
            : sprintf('%d:%02d', $m, $sec);
    }
}
