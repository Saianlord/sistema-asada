<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\ViabilityModelConfiguration;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'criticality',
        'priority',
        'user_id',
        'technical_justification',
        'estimated_cost',
        'impact',
        'risk',
        'evidence_path',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(ProjectEvaluation::class);
    }

    public function getAverageViabilityScoreAttribute(): ?float
    {
        if ($this->evaluations->isEmpty()) {
            return null;
        }

        return round($this->evaluations->avg('average_score'), 2);
    }

    public function getViabilityLabelAttribute(): ?string
    {
        $score = $this->average_viability_score;

        if ($score === null) {
            return null;
        }

        $config = ViabilityModelConfiguration::getActive();

        return match (true) {
            $score >= $config->viable_threshold => 'viable',
            $score >= $config->conditional_threshold => 'conditional',
            default => 'not_viable',
        };
    }
}
