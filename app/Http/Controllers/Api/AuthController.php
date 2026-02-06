<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Traits\ApiResponse;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'mitra_id' => $request->mitra_id,
            'status' => 'active'
        ]);

        $token = $user->createToken('auth_token')->accessToken;

        return $this->successResponse([
            'user' => $user,
            'token' => $token
        ], 'User registered successfully', 201);
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->errorResponse('Invalid credentials', null, 401);
        }

        if ($user->status !== 'active') {
            return $this->errorResponse('Account is inactive', null, 403);
        }

        $token = $user->createToken('auth_token')->accessToken;

        return $this->successResponse([
            'user' => $user,
            'token' => $token
        ], 'Login successful');
    }

    public function logout()
    {
        auth()->user()->token()->revoke();
        
        return $this->successResponse(null, 'Logout successful');
    }

    public function me()
    {
        return $this->successResponse(auth()->user(), 'User data retrieved');
    }
}
