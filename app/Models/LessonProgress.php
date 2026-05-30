<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class LessonProgress extends Model
{
    protected $table = 'lesson_progress';
    protected $fillable = ['student_id','lesson_id','course_id','completed','watch_seconds','completed_at'];
    protected $casts = ['completed' => 'boolean', 'completed_at' => 'datetime'];

    public function student() { return $this->belongsTo(User::class, 'student_id'); }
    public function lesson()  { return $this->belongsTo(Lesson::class); }
    public function course()  { return $this->belongsTo(Course::class); }
}
