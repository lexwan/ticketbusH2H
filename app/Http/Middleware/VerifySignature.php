<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifySignature
{
    public function handle(Request $request, Closure $next)
    {
        $signature = $request->header('X-Signature');
        
        if (!$signature) {
            return response()->json([
                'status' => false,
                'message' => 'Signature required'
            ], 401);
        }

        $payload = $request->getContent();
        $secret = config('app.callback_secret');
        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        if (!hash_equals($expectedSignature, $signature)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid signature'
            ], 401);
        }

        return $next($request);
    }
}
