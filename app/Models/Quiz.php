<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $fillable = [
        'course_id','lesson_id','teacher_id','title','description',
        'time_limit_minutes','pass_percentage','max_attempts',
        'show_answers_after','is_published','randomize','questions_per_attempt',
    ];
    protected $casts = ['is_published' => 'boolean', 'show_answers_after' => 'boolean', 'randomize' => 'boolean'];

    public function course()    { return $this->belongsTo(Course::class); }
    public function lesson()    { return $this->belongsTo(Lesson::class); }
    public function teacher()   { return $this->belongsTo(User::class, 'teacher_id'); }
    public function questions() { return $this->hasMany(QuizQuestion::class)->orderBy('sort_order'); }
    public function attempts()  { return $this->hasMany(QuizAttempt::class); }
}
