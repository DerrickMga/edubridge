<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    protected $fillable = [
        'course_id','lesson_id','uploaded_by','title','description',
        'type','storage_path','external_url','mime_type',
        'file_size_bytes','is_downloadable','download_count','sort_order',
    ];

    public function course()  { return $this->belongsTo(Course::class); }
    public function lesson()  { return $this->belongsTo(Lesson::class); }
    public function uploader(){ return $this->belongsTo(User::class, 'uploaded_by'); }

    public function getUrlAttribute(): ?string
    {
        if ($this->external_url) return $this->external_url;
        if ($this->storage_path) return \Storage::url($this->storage_path);
        return null;
    }

    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size_bytes;
        if ($bytes >= 1_073_741_824) return round($bytes / 1_073_741_824, 1).' GB';
        if ($bytes >= 1_048_576)     return round($bytes / 1_048_576, 1).' MB';
        if ($bytes >= 1_024)         return round($bytes / 1_024, 1).' KB';
        return $bytes.' B';
    }
}
