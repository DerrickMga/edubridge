<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionAttendance extends Model
{
    protected $fillable = ['live_session_id', 'user_id', 'joined_at'];

    protected $casts = ['joined_at' => 'datetime'];

    public function session() { return $this->belongsTo(LiveSession::class, 'live_session_id'); }
    public function user()    { return $this->belongsTo(User::class); }
}
