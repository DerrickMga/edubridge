<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class XpEvent extends Model
{
    protected $fillable = ['user_id', 'event_type', 'xp', 'description', 'reference_type', 'reference_id'];

    public function user()      { return $this->belongsTo(User::class); }
    public function reference() { return $this->morphTo(); }
}
