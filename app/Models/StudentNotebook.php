<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentNotebook extends Model
{
    protected $fillable = [
        'user_id', 'conversation_id', 'type', 'title', 'subject',
        'level', 'topic', 'content', 'youtube_videos', 'tags', 'is_pinned',
    ];

    protected $casts = [
        'youtube_videos' => 'array',
        'tags'           => 'array',
        'is_pinned'      => 'boolean',
    ];

    public function user()         { return $this->belongsTo(User::class); }
    public function conversation() { return $this->belongsTo(Conversation::class); }

    /** Return decoded JSON content if this is a plan, otherwise raw markdown. */
    public function getPlanAttribute(): array
    {
        if ($this->type === 'notes') return [];
        $data = json_decode($this->content, true);
        return is_array($data) ? $data : [];
    }

    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            'study_plan'    => '📅',
            'advanced_plan' => '🗺️',
            default         => '📝',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'study_plan'    => 'Study Plan',
            'advanced_plan' => 'Advanced Study Plan',
            default         => 'Notes',
        };
    }
}
