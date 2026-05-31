<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['user_id','course_id','amount','currency','provider','provider_reference','status','metadata','access_period'];
    protected $casts = ['metadata' => 'array'];
    public function user()   { return $this->belongsTo(User::class); }
    public function course() { return $this->belongsTo(Course::class); }
}
