<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $fillable = ['course_id','title','description','video_url','youtube_video_id','duration_seconds','order','status','attachments','release_after_days'];
    protected $casts = ['attachments' => 'array', 'release_after_days' => 'integer'];
    public function course() { return $this->belongsTo(Course::class); }
    public function resources() { return $this->hasMany(Resource::class)->orderBy('sort_order'); }
}
