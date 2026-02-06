<?php

namespace App\Http\Traits;

trait ApiResponse
{
    protected function successResponse($data = null, $message = 'success', $code = 200)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function errorResponse($message = 'error', $errors = null, $code = 400)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'errors' => $errors
        ], $code);
    }
}
