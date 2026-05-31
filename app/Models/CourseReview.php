<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseReview extends Model
{
    protected $fillable = ['course_id', 'user_id', 'rating', 'title', 'body', 'is_visible'];
    protected $casts = ['rating' => 'integer', 'is_visible' => 'boolean'];

    public function course() { return $this->belongsTo(Course::class); }
    public function user()   { return $this->belongsTo(User::class); }
}
