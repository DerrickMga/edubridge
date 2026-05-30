<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Certificate extends Model
{
    protected $fillable = ['student_id','course_id','certificate_number','final_score','pdf_path','issued_at'];
    protected $casts = ['issued_at' => 'datetime'];

    public function student() { return $this->belongsTo(User::class, 'student_id'); }
    public function course()  { return $this->belongsTo(Course::class); }

    public static function generateNumber(): string
    {
        return 'EB-'.strtoupper(Str::random(4)).'-'.date('Y').'-'.rand(1000, 9999);
    }
}
