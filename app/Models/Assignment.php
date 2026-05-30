<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $fillable = [
        'course_id','lesson_id','teacher_id','title','instructions',
        'type','max_score','due_at','allow_late','is_published',
    ];
    protected $casts = ['due_at' => 'datetime', 'is_published' => 'boolean', 'allow_late' => 'boolean'];

    public function course()      { return $this->belongsTo(Course::class); }
    public function lesson()      { return $this->belongsTo(Lesson::class); }
    public function teacher()     { return $this->belongsTo(User::class, 'teacher_id'); }
    public function submissions() { return $this->hasMany(AssignmentSubmission::class); }

    public function submissionFor(int $studentId): ?AssignmentSubmission
    {
        return $this->submissions()->where('student_id', $studentId)->first();
    }
}
