<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherPolicy extends Model
{
    protected $fillable = [
        'slug', 'title', 'category', 'version', 'body',
        'is_active', 'effective_at', 'created_by',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'effective_at' => 'datetime',
    ];

    public function author()           { return $this->belongsTo(User::class, 'created_by'); }
    public function acknowledgements() { return $this->hasMany(TeacherPolicyAcknowledgement::class, 'policy_id'); }

    public function scopeActive($q)         { return $q->where('is_active', true); }
    public function scopeCategory($q, $c)   { return $q->where('category', $c); }

    /** Latest active version per slug. */
    public static function currentBySlug(): \Illuminate\Support\Collection
    {
        return self::active()
            ->orderBy('slug')
            ->orderByDesc('version')
            ->get()
            ->groupBy('slug')
            ->map(fn ($group) => $group->first());
    }
}
