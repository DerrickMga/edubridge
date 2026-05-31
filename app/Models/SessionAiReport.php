<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionAiReport extends Model
{
    protected $fillable = [
        'live_session_id', 'quiz_id', 'attendees_count',
        'summary', 'action_items', 'transcript_excerpt',
        'error', 'processed_at',
    ];

    protected $casts = [
        'action_items' => 'array',
        'processed_at' => 'datetime',
    ];

    public function session() { return $this->belongsTo(LiveSession::class, 'live_session_id'); }
    public function quiz()    { return $this->belongsTo(Quiz::class); }
}
