<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function (Model $model) {
            $model->audit('created');
        });

        static::updated(function (Model $model) {
            if ($model->isDirty('status')) {
                $model->audit('status_changed');
            } else {
                $model->audit('updated');
            }
        });

        static::deleted(function (Model $model) {
            $model->audit('deleted');
        });
    }

    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'auditable')->latest();
    }

    protected function audit($action)
    {
        if (app()->runningInConsole() && !app()->runningUnitTests()) {
            return;
        }

        $oldValues = [];
        $newValues = [];

        if (in_array($action, ['updated', 'status_changed'])) {
            $oldValues = array_intersect_key($this->getOriginal(), $this->getDirty());
            $newValues = $this->getDirty();
        } elseif ($action === 'created') {
            $newValues = $this->getAttributes();
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'action_type' => $action,
            'auditable_type' => static::class,
            'auditable_id' => $this->getKey(),
            'old_values' => empty($oldValues) ? null : $oldValues,
            'new_values' => empty($newValues) ? null : $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
