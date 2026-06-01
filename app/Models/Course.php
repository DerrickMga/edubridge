<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'teacher_id','title','description','subject','grade_level',
        'thumbnail','status','price_usd','price_zwg','youtube_playlist_id',
        'average_rating','reviews_count','prerequisite_course_id',
    ];

    protected $casts = [
        'average_rating' => 'decimal:2',
        'reviews_count'  => 'integer',
    ];

    public function teacher()       { return $this->belongsTo(User::class, 'teacher_id'); }
    public function prerequisite()   { return $this->belongsTo(Course::class, 'prerequisite_course_id'); }
    public function teachers()      { return $this->belongsToMany(User::class, 'course_teacher', 'course_id', 'teacher_id')->withPivot('role', 'hourly_rate_usd')->withTimestamps(); }
    public function lessons()       { return $this->hasMany(Lesson::class)->orderBy('order'); }
    public function liveSessions()  { return $this->hasMany(LiveSession::class); }
    public function enrollments()   { return $this->belongsToMany(User::class, 'enrollments')->withTimestamps()->withPivot('status', 'access_period', 'expires_at'); }
    public function payments()      { return $this->hasMany(Payment::class); }
    public function resources()     { return $this->hasMany(Resource::class)->orderBy('sort_order'); }
    public function assignments()   { return $this->hasMany(Assignment::class); }
    public function quizzes()       { return $this->hasMany(Quiz::class); }
    public function recordings()    { return $this->hasMany(Recording::class); }
    public function discussions()   { return $this->hasMany(Discussion::class)->whereNull('parent_id')->orderByDesc('is_pinned')->latest(); }
    public function announcements() { return $this->hasMany(Announcement::class); }
    public function certificates()  { return $this->hasMany(Certificate::class); }
    public function reviews()       { return $this->hasMany(CourseReview::class)->latest(); }
    public function wishlistedBy()  { return $this->belongsToMany(User::class, 'wishlists')->withTimestamps(); }

    public function scopePublished($q) { return $q->where('status', 'published'); }

    public function progressFor(int $studentId): int
    {
        $total = $this->lessons()->count();
        if ($total === 0) return 0;
        $done = LessonProgress::where('student_id', $studentId)
            ->where('course_id', $this->id)
            ->where('completed', true)
            ->count();
        return (int) round(($done / $total) * 100);
    }
}
