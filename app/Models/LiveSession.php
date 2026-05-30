<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class LiveSession extends Model
{
    protected $fillable = [
        'course_id','teacher_id','title','scheduled_at','duration_minutes',
        'provider','meeting_id','meeting_url','recording_url','status',
        'is_recorded','breakout_rooms_enabled','breakout_room_count',
        'attendees_count','teacher_notes',
    ];
    protected $casts = [
        'scheduled_at'           => 'datetime',
        'is_recorded'            => 'boolean',
        'breakout_rooms_enabled' => 'boolean',
    ];

    public function course()     { return $this->belongsTo(Course::class); }
    public function teacher()    { return $this->belongsTo(User::class, 'teacher_id'); }
    public function recordings() { return $this->hasMany(Recording::class); }

    public function scopeUpcoming($q)
    {
        return $q->where('scheduled_at', '>', now())
                 ->whereIn('status', ['scheduled','ongoing'])
                 ->orderBy('scheduled_at');
    }

    public function getIsStartingSoonAttribute(): bool
    {
        return $this->scheduled_at->isFuture()
            && $this->scheduled_at->diffInMinutes(now()) <= 60;
    }
}
