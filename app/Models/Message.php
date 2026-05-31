<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['conversation_id', 'role', 'model_used', 'content', 'metadata'];
    protected $casts = ['metadata' => 'array'];
    public function conversation() { return $this->belongsTo(Conversation::class); }
}
