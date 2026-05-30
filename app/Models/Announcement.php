<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = ['course_id','author_id','title','body','audience','published_at'];
    protected $casts = ['published_at' => 'datetime'];

    public function course() { return $this->belongsTo(Course::class); }
    public function author() { return $this->belongsTo(User::class, 'author_id'); }

    public function scopePublished($q) { return $q->where('published_at', '<=', now()); }
}
