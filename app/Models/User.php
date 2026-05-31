<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'phone', 'country', 'grade_level', 'is_active', 'hourly_rate_usd',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    public function isAdmin(): bool    { return $this->role === 'admin'; }
    public function isTeacher(): bool  { return $this->role === 'teacher'; }
    public function isStudent(): bool  { return $this->role === 'student'; }

    public function courses()       { return $this->hasMany(Course::class, 'teacher_id'); }
    public function payments()      { return $this->hasMany(Payment::class); }
    public function conversations() { return $this->hasMany(Conversation::class); }
    public function enrollments()   { return $this->belongsToMany(Course::class, 'enrollments')->withTimestamps(); }
    public function sessionLogs()   { return $this->hasMany(SessionLog::class, 'teacher_id'); }
    public function paymentItems()  { return $this->hasMany(TeacherPaymentItem::class, 'teacher_id'); }

    // Gamification
    public function stat()    { return $this->hasOne(StudentStat::class); }
    public function badges()  { return $this->belongsToMany(Badge::class, 'student_badges')->withPivot('earned_at')->orderByPivot('earned_at', 'desc'); }
    public function xpEvents(){ return $this->hasMany(XpEvent::class); }

    public function getXpAttribute(): int   { return $this->stat?->xp ?? 0; }
    public function getLevelAttribute(): int { return $this->stat?->level ?? 1; }
    public function getStreakAttribute(): int { return $this->stat?->streak_days ?? 0; }
}
