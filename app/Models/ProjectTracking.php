<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectTracking extends Model
{
    use HasFactory;

    protected $table = 'project_trackings';

    protected $fillable = [
        'project_id',
        'type',
        'title',
        'description',
        'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function getTypeLabelAttribute(): string
    {
        $labels = [
            'milestone' => 'Hito',
            'progress' => 'Avance',
            'incident' => 'Incidencia',
        ];

        return $labels[$this->type] ?? $this->type;
    }
}
