<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Log business action into audit_logs table
     */
    public static function log(
        string $action,
        string $module,
        string|int|null $recordId,
        string $summary,
        ?array $oldValues = null,
        ?array $newValues = null
    ): AuditLog {
        // Strip sensitive fields
        $sensitiveKeys = ['password', 'remember_token', 'token', 'signature_data'];
        if ($oldValues) {
            foreach ($sensitiveKeys as $k) {
                unset($oldValues[$k]);
            }
        }
        if ($newValues) {
            foreach ($sensitiveKeys as $k) {
                unset($newValues[$k]);
            }
        }

        return AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'module' => $module,
            'record_id' => $recordId ? (string) $recordId : null,
            'summary' => $summary,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'created_at' => now(),
        ]);
    }
}
