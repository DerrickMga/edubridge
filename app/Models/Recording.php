<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Recording extends Model
{
    protected $fillable = [
        'live_session_id','course_id','lesson_id','teacher_id','title',
        'storage_path','external_url','source','duration_seconds',
        'file_size_bytes','status','is_public',
        'youtube_video_id','youtube_url','youtube_status','youtube_error',
        'youtube_privacy','thumbnail_url',
    ];

    public function liveSession() { return $this->belongsTo(LiveSession::class); }
    public function course()      { return $this->belongsTo(Course::class); }
    public function lesson()      { return $this->belongsTo(Lesson::class); }
    public function teacher()     { return $this->belongsTo(User::class, 'teacher_id'); }

    public function getUrlAttribute(): ?string
    {
        if ($this->youtube_url) return $this->youtube_url;
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

    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = (int) $this->file_size_bytes;
        if ($bytes <= 0) return '—';
        foreach (['B','KB','MB','GB','TB'] as $unit) {
            if ($bytes < 1024) return number_format($bytes, $bytes < 10 && $unit !== 'B' ? 1 : 0).' '.$unit;
            $bytes /= 1024;
        }
        return number_format($bytes, 1).' PB';
    }

    public function hasYouTube(): bool
    {
        return ! empty($this->youtube_video_id);
    }
}
