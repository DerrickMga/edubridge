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
        'name', 'email', 'password', 'role', 'phone', 'country', 'city',
        'grade_level', 'is_active', 'hourly_rate_usd',
        'avatar', 'bio', 'website', 'linkedin_url', 'twitter_handle', 'qualification',
        'last_seen_at', 'availability_status', 'accepts_assignments', 'timezone',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at'    => 'datetime',
            'password'             => 'hashed',
            'is_active'            => 'boolean',
            'last_seen_at'         => 'datetime',
            'accepts_assignments'  => 'boolean',
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
    public function verification()      { return $this->hasOne(TeacherVerification::class, 'teacher_id'); }
    public function settlements()       { return $this->hasMany(SettlementRequest::class, 'teacher_id'); }
    public function equipmentProfile()  { return $this->hasOne(TeacherEquipmentProfile::class, 'teacher_id'); }
    public function equipmentLoans()    { return $this->hasMany(EquipmentLoanApplication::class, 'teacher_id'); }

    // Workforce / Rota
    public function taughtCourses()     { return $this->belongsToMany(Course::class, 'course_teacher', 'teacher_id', 'course_id')->withPivot('role', 'hourly_rate_usd')->withTimestamps(); }
    public function shifts()            { return $this->hasMany(TeacherShift::class, 'teacher_id'); }
    public function availabilityWindows(){ return $this->hasMany(TeacherAvailability::class, 'teacher_id'); }
    public function timeOff()           { return $this->hasMany(TeacherTimeOff::class, 'teacher_id'); }

    // Policy & contract
    public function contracts()          { return $this->hasMany(TeacherContract::class, 'teacher_id'); }
    public function policyAcknowledgements() { return $this->hasMany(TeacherPolicyAcknowledgement::class, 'teacher_id'); }
    public function riskIncidents()      { return $this->hasMany(PolicyRiskIncident::class, 'teacher_id'); }

    // Marketplace / engagement
    public function reviews()            { return $this->hasMany(CourseReview::class); }
    public function wishlist()           { return $this->belongsToMany(Course::class, 'wishlists')->withTimestamps(); }
    public function lessonNotes()        { return $this->hasMany(LessonNote::class); }
    public function refundRequests()     { return $this->hasMany(RefundRequest::class); }
    public function couponRedemptions()  { return $this->hasMany(CouponRedemption::class); }

    public function activeContract(): ?TeacherContract
    {
        return $this->contracts()->where('status', 'signed')->latest('signed_at')->first();
    }

    public function hasSignedContract(): bool
    {
        $c = $this->activeContract();
        return $c !== null && ! $c->isExpired();
    }

    public function isOnline(): bool
    {
        if ($this->availability_status === 'offline') return false;
        return $this->last_seen_at && $this->last_seen_at->gt(now()->subMinutes(5));
    }

    public function getEffectiveHourlyRateAttribute(): float
    {
        return (float) ($this->hourly_rate_usd ?? 0);
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if ($this->avatar) {
            return \Illuminate\Support\Facades\Storage::url($this->avatar);
        }
        return null;
    }

    public function getIsVerifiedAttribute(): bool
    {
        return $this->verification?->status === 'approved';
    }

    // Gamification
    public function stat()    { return $this->hasOne(StudentStat::class); }
    public function badges()  { return $this->belongsToMany(Badge::class, 'student_badges')->withPivot('earned_at')->orderByPivot('earned_at', 'desc'); }
    public function xpEvents(){ return $this->hasMany(XpEvent::class); }

    public function getXpAttribute(): int   { return $this->stat?->xp ?? 0; }
    public function getLevelAttribute(): int { return $this->stat?->level ?? 1; }
    public function getStreakAttribute(): int { return $this->stat?->streak_days ?? 0; }
}
