<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LearningMemory extends Model
{
    protected $fillable = ['conversation_id', 'user_id', 'summary', 'topics_seen', 'weak_areas', 'strengths'];
    protected $casts    = ['topics_seen' => 'array', 'weak_areas' => 'array', 'strengths' => 'array'];

    public function conversation() { return $this->belongsTo(Conversation::class); }
    public function user()         { return $this->belongsTo(User::class); }
}
