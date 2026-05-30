<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentStat extends Model
{
    protected $fillable = [
        'user_id', 'xp', 'level', 'streak_days', 'longest_streak', 'last_active_date',
    ];

    protected $casts = [
        'last_active_date' => 'date',
    ];

    public function user() { return $this->belongsTo(User::class); }

    // XP thresholds per level (level index = level - 1)
    const LEVEL_THRESHOLDS = [0, 100, 250, 500, 800, 1200, 1800, 2500, 3500, 5000];

    public static function levelFromXp(int $xp): int
    {
        $level = 1;
        foreach (self::LEVEL_THRESHOLDS as $i => $threshold) {
            if ($xp >= $threshold) $level = $i + 1;
        }
        return min($level, 10);
    }

    public function getNextLevelXpAttribute(): int
    {
        $next = $this->level; // next level index (0-based)
        return self::LEVEL_THRESHOLDS[$next] ?? self::LEVEL_THRESHOLDS[array_key_last(self::LEVEL_THRESHOLDS)];
    }

    public function getCurrentLevelXpAttribute(): int
    {
        return self::LEVEL_THRESHOLDS[max(0, $this->level - 1)];
    }

    public function getLevelProgressPercentAttribute(): int
    {
        $curr = $this->current_level_xp;
        $next = $this->next_level_xp;
        if ($next <= $curr) return 100;
        return (int) round(($this->xp - $curr) / ($next - $curr) * 100);
    }
}
