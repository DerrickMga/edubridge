<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = ['user_id', 'title', 'channel', 'preferred_model', 'subject', 'level'];

    public function user()          { return $this->belongsTo(User::class); }
    public function messages()      { return $this->hasMany(Message::class)->orderBy('created_at'); }
    public function learningMemory(){ return $this->hasOne(LearningMemory::class); }
}
