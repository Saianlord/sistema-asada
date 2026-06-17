<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'technical_score',
        'financial_score',
        'operational_score',
        'regulatory_score',
        'average_score',
        'viability_status',
    ];

    protected static function booted(): void
    {
        static::saving(function (ProjectEvaluation $evaluation) {
            $evaluation->average_score = round(
                ($evaluation->technical_score + $evaluation->financial_score + $evaluation->operational_score + $evaluation->regulatory_score) / 4,
                2
            );

            $evaluation->viability_status = match (true) {
                $evaluation->average_score >= 7.0 => 'viable',
                $evaluation->average_score >= 4.0 => 'conditional',
                default => 'not_viable',
            };
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
