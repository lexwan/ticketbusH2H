<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RolePermission
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!$request->user()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        if (!$request->user()->role || !in_array($request->user()->role->name, $roles)) {
            return response()->json([
                'status' => false,
                'message' => 'Forbidden - Insufficient permissions'
            ], 403);
        }

        return $next($request);
    }
}
