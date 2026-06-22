<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\ViabilityModelConfiguration;

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
            $config = ViabilityModelConfiguration::getActive();

            $evaluation->average_score = round(
                (($evaluation->technical_score * $config->technical_weight) +
                 ($evaluation->financial_score * $config->financial_weight) +
                 ($evaluation->operational_score * $config->operational_weight) +
                 ($evaluation->regulatory_score * $config->regulatory_weight)) / 100,
                2
            );

            $evaluation->viability_status = match (true) {
                $evaluation->average_score >= $config->viable_threshold => 'viable',
                $evaluation->average_score >= $config->conditional_threshold => 'conditional',
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
