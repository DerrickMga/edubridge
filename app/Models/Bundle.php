<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Bundle extends Model
{
    protected $fillable = ['title', 'slug', 'description', 'thumbnail', 'price_usd', 'price_zwg', 'is_active', 'created_by'];
    protected $casts = ['is_active' => 'boolean', 'price_usd' => 'decimal:2', 'price_zwg' => 'decimal:2'];

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'bundle_course')->withPivot('sort_order')->orderBy('bundle_course.sort_order');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
