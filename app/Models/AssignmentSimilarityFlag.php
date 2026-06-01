<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentSimilarityFlag extends Model
{
    protected $fillable = ['submission_id', 'compared_submission_id', 'similarity_score'];
    protected $casts = ['similarity_score' => 'decimal:2'];

    public function submission(): BelongsTo { return $this->belongsTo(AssignmentSubmission::class, 'submission_id'); }
    public function comparedSubmission(): BelongsTo { return $this->belongsTo(AssignmentSubmission::class, 'compared_submission_id'); }
}
