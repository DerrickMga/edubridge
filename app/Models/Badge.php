<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    protected $fillable = ['slug', 'name', 'description', 'icon', 'colour'];

    public function students() { return $this->belongsToMany(User::class, 'student_badges')->withPivot('earned_at'); }
}
