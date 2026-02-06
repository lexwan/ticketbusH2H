<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Http\Traits\ApiResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponse;

    /**
     * Login (Admin & Mitra Only)
     * POST /api/v1/auth/login
     */
    public function login(LoginRequest $request)
    {
        $user = User::with(['role', 'mitra'])
            ->where('email', $request->email)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->errorResponse('Invalid credentials', null, 401);
        }

        if ($user->status !== 'active') {
            return $this->errorResponse('Account is inactive', null, 403);
        }

        // Validasi role: hanya admin atau mitra
        if (!in_array($user->role?->name, ['admin', 'mitra'])) {
            return $this->errorResponse('Unauthorized role', null, 403);
        }

        $token = $user->createToken('auth_token')->accessToken;

        return $this->successResponse([
            'user' => new UserResource($user),
            'access_token' => $token,
            'token_type' => 'Bearer'
        ], 'Login successful');
    }

    /**
     * Logout
     * POST /api/v1/auth/logout
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        
        return $this->successResponse(null, 'Logout successful');
    }

    /**
     * Refresh Token
     * POST /api/v1/auth/refresh
     */
    public function refresh(Request $request)
    {
        $user = $request->user();
        $request->user()->token()->revoke();
        
        $token = $user->createToken('auth_token')->accessToken;

        return $this->successResponse([
            'access_token' => $token,
            'token_type' => 'Bearer'
        ], 'Token refreshed successfully');
    }

    /**
     * Get Current User
     * GET /api/v1/auth/me
     */
    public function me(Request $request)
    {
        $user = User::with(['role', 'mitra'])->find($request->user()->id);

        return $this->successResponse(
            new UserResource($user),
            'User data retrieved successfully'
        );
    }

    /**
     * Get User Permissions
     * GET /api/v1/auth/permissions
     */
    public function permissions(Request $request)
    {
        $user = $request->user();
        $user->load('role');
        
        $permissions = [
            'role' => $user->role?->name,
            'permissions' => $this->getUserPermissions($user)
        ];

        return $this->successResponse(
            $permissions,
            'Permissions retrieved successfully'
        );
    }

    /**
     * Helper: Get permissions based on role
     */
    private function getUserPermissions($user)
    {
        $roleName = $user->role?->name;

        $rolePermissions = [
            'admin' => [
                'users.view', 'users.create', 'users.update', 'users.delete',
                'mitra.view', 'mitra.register', 'mitra.fee',
                'topups.view', 'topups.approve', 'topups.reject',
                'transactions.view', 'transactions.cancel',
                'reports.view', 'dashboard.admin'
            ],
            'mitra' => [
                'transactions.search', 'transactions.book', 'transactions.pay',
                'transactions.view', 'transactions.issue',
                'balance.view', 'topup.create', 'topup.view',
                'dashboard.partner'
            ]
        ];

        return $rolePermissions[$roleName] ?? [];
    }
}
