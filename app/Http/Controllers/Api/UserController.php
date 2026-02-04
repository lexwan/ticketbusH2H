<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Services\ActivityLogService;
use App\Services\UserService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use ApiResponse;

    public function __construct(
        private UserService $userService,
        private ActivityLogService $activityLogService
    ) {}

    /**
     * Get user profile.
     */
    public function profile(Request $request): JsonResponse
    {
        $profileData = $this->userService->getProfileWithActivities($request->user());
        
        return $this->successResponse(
            $profileData,
            'Profile retrieved successfully'
        );
    }

    /**
     * Update user profile.
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $this->userService->updateProfile(
            $request->user(),
            $request->validated()
        );
        
        return $this->successResponse(
            $user,
            'Profile updated successfully'
        );
    }

    /**
     * Upload user avatar.
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048']
        ]);
        
        $avatarUrl = $this->userService->uploadAvatar(
            $request->user(),
            $request->file('avatar')
        );
        
        return $this->successResponse(
            ['avatar_url' => $avatarUrl],
            'Avatar uploaded successfully'
        );
    }

    /**
     * Delete user avatar.
     */
    public function deleteAvatar(Request $request): JsonResponse
    {
        $deleted = $this->userService->deleteAvatar($request->user());
        
        if ($deleted) {
            return $this->successResponse(
                null,
                'Avatar deleted successfully'
            );
        }
        
        return $this->errorResponse('No avatar to delete', 404);
    }

    /**
     * Get user activity logs.
     */
    public function activities(Request $request): JsonResponse
    {
        $activities = $this->activityLogService->getUserActivities(
            $request->user()->id,
            $request->get('per_page', 15)
        );
        
        return $this->paginatedResponse(
            $activities,
            'Activities retrieved successfully'
        );
    }
}
