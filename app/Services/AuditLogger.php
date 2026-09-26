<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    /**
     * Record an audit log entry for user action
     */
    public static function log(string $action, string $description, ?object $model = null, ?array $details = null, ?int $userId = null): AuditLog
    {
        $userId = $userId ?? auth()->id();

        return AuditLog::create([
            'user_id' => $userId,
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model ? ($model->id ?? null) : null,
            'description' => $description,
            'details' => $details,
            'ip_address' => Request::ip(),
        ]);
    }
}
