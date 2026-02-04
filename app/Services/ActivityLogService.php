<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogService
{
    /**
     * Log user activity.
     *
     * @param string $action
     * @param string $description
     * @param array|null $properties
     * @return ActivityLog
     */
    public function logActivity(string $action, string $description, ?array $properties = null): ActivityLog
    {
        return ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'properties' => $properties
        ]);
    }

    /**
     * Get user activity logs.
     *
     * @param int|null $userId
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getUserActivities(?int $userId = null, int $perPage = 15)
    {
        $query = ActivityLog::with('user')->latest();
        
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        return $query->paginate($perPage);
    }

    /**
     * Clear old activity logs.
     *
     * @param int $days
     * @return int
     */
    public function clearOldLogs(int $days = 30): int
    {
        return ActivityLog::where('created_at', '<', now()->subDays($days))->delete();
    }
}