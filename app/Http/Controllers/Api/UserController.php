<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Services\ActivityLogService;
use App\Services\UserService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
     * Update user profile (with optional avatar and password change).
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();
        
        try {
            // Handle avatar upload if present
            if ($request->hasFile('avatar')) {
                $request->validate([
                    'avatar' => ['image', 'mimes:jpeg,png,jpg,gif', 'max:2048']
                ]);
                
                $avatarUrl = $this->userService->uploadAvatar(
                    $user,
                    $request->file('avatar')
                );
            }
            
            // Update profile data (exclude avatar file from data)
            $profileData = collect($data)->except('avatar')->toArray();
            if (!empty($profileData)) {
                $user = $this->userService->updateProfile($user, $profileData);
            }
            
            // Get updated profile with avatar URL
            $responseData = $user->fresh()->toArray();
            $responseData['avatar_url'] = $user->avatar ? Storage::disk('public')->url($user->avatar) : null;
            
            $message = 'Profile updated successfully';
            if (isset($data['new_password'])) {
                $message = 'Profile and password updated successfully';
            }
            
            return $this->successResponse(
                $responseData,
                $message
            );
            
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
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
