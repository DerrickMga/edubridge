<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoogleToken extends Model
{
    protected $fillable = [
        'user_id', 'scope', 'access_token', 'refresh_token',
        'expires_at', 'channel_id', 'channel_title',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    protected $hidden = ['access_token', 'refresh_token'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return ! $this->expires_at || $this->expires_at->isPast();
    }
}
