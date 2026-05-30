<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class LiveSession extends Model
{
    protected $fillable = ['course_id','teacher_id','title','scheduled_at','duration_minutes','provider','meeting_id','meeting_url','recording_url','status'];
    protected $casts = ['scheduled_at' => 'datetime'];
    public function course()  { return $this->belongsTo(Course::class); }
    public function teacher() { return $this->belongsTo(User::class, 'teacher_id'); }
}
