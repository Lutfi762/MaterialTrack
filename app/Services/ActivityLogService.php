<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogService
{
    public static function log(string $action, $subject = null, array $properties = [], ?Request $request = null, ?int $userId = null): ActivityLog
    {
        $request = $request ?? request();

        return ActivityLog::create([
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->id,
            'properties' => $properties,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    public static function getLogs($query = null, int $limit = 50)
    {
        $query = $query ?? ActivityLog::with('user');

        return $query->latest()->paginate($limit);
    }
}
