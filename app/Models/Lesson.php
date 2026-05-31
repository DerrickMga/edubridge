<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $fillable = ['course_id','title','description','video_url','youtube_video_id','duration_seconds','order','status','attachments'];
    protected $casts = ['attachments' => 'array'];
    public function course() { return $this->belongsTo(Course::class); }
    public function resources() { return $this->hasMany(Resource::class)->orderBy('sort_order'); }
}
