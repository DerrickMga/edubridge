<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    protected $fillable = [
        'quiz_id','student_id','attempt_number','answers',
        'score','max_score','passed','started_at','completed_at',
    ];
    protected $casts = [
        'answers'      => 'array',
        'passed'       => 'boolean',
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function quiz()    { return $this->belongsTo(Quiz::class); }
    public function student() { return $this->belongsTo(User::class, 'student_id'); }

    public function getScorePercentageAttribute(): int
    {
        if ($this->max_score === 0) return 0;
        return (int) round(($this->score / $this->max_score) * 100);
    }
}
