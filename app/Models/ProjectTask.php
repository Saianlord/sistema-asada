<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'due_date',
        'assigned_user_id',
        'status',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'pending' => 'Pendiente',
            'in_progress' => 'En progreso',
            'completed' => 'Completada',
        ];

        return $labels[$this->status] ?? $this->status;
    }
}
