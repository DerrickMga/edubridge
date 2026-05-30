<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;
    protected $fillable = ['teacher_id','title','description','subject','grade_level','thumbnail','status','price_usd','price_zwg','youtube_playlist_id'];

    public function teacher()      { return $this->belongsTo(User::class, 'teacher_id'); }
    public function lessons()      { return $this->hasMany(Lesson::class)->orderBy('order'); }
    public function liveSessions() { return $this->hasMany(LiveSession::class); }
    public function enrollments()  { return $this->belongsToMany(User::class, 'enrollments')->withTimestamps(); }
    public function payments()     { return $this->hasMany(Payment::class); }

    public function scopePublished($q) { return $q->where('status', 'published'); }
}
