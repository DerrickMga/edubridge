<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Recording extends Model
{
    protected $fillable = [
        'live_session_id','course_id','teacher_id','title',
        'storage_path','external_url','source','duration_seconds',
        'file_size_bytes','status','is_public',
    ];

    public function liveSession() { return $this->belongsTo(LiveSession::class); }
    public function course()      { return $this->belongsTo(Course::class); }
    public function teacher()     { return $this->belongsTo(User::class, 'teacher_id'); }

    public function getUrlAttribute(): ?string
    {
        if ($this->external_url) return $this->external_url;
        if ($this->storage_path) return \Storage::url($this->storage_path);
        return null;
    }

    public function getDurationFormattedAttribute(): string
    {
        $m = intdiv($this->duration_seconds, 60);
        $s = $this->duration_seconds % 60;
        return sprintf('%d:%02d', $m, $s);
    }
}
