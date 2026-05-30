<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Discussion extends Model
{
    use SoftDeletes;
    protected $fillable = ['course_id','lesson_id','author_id','parent_id','title','body','is_pinned','is_resolved','upvotes'];
    protected $casts = ['is_pinned' => 'boolean', 'is_resolved' => 'boolean'];

    public function course()  { return $this->belongsTo(Course::class); }
    public function lesson()  { return $this->belongsTo(Lesson::class); }
    public function author()  { return $this->belongsTo(User::class, 'author_id'); }
    public function parent()  { return $this->belongsTo(Discussion::class, 'parent_id'); }
    public function replies() { return $this->hasMany(Discussion::class, 'parent_id')->latest(); }
}
