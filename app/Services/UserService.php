<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserService
{
    public function __construct(
        private ActivityLogService $activityLogService
    ) {}

    /**
     * Update user profile.
     *
     * @param User $user
     * @param array $data
     * @return User
     */
    public function updateProfile(User $user, array $data): User
    {
        $originalData = $user->toArray();
        
        // Handle avatar upload if present
        if (isset($data['avatar']) && $data['avatar'] instanceof UploadedFile) {
            // Delete old avatar if exists
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            
            // Store new avatar
            $path = $data['avatar']->store('avatars', 'public');
            $data['avatar'] = $path;
        }
        
        $user->update($data);
        
        // Log activity
        $this->activityLogService->logActivity(
            'profile_updated',
            'User updated their profile',
            [
                'original' => $originalData,
                'updated' => $user->fresh()->toArray()
            ]
        );
        
        return $user->fresh();
    }

    /**
     * Upload user avatar.
     *
     * @param User $user
     * @param UploadedFile $file
     * @return string
     */
    public function uploadAvatar(User $user, UploadedFile $file): string
    {
        // Delete old avatar if exists
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }
        
        // Store new avatar
        $path = $file->store('avatars', 'public');
        
        // Update user avatar path
        $user->update(['avatar' => $path]);
        
        // Log activity
        $this->activityLogService->logActivity(
            'avatar_uploaded',
            'User uploaded a new avatar'
        );
        
        return Storage::disk('public')->url($path);
    }

    /**
     * Delete user avatar.
     *
     * @param User $user
     * @return bool
     */
    public function deleteAvatar(User $user): bool
    {
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->update(['avatar' => null]);
            
            // Log activity
            $this->activityLogService->logActivity(
                'avatar_deleted',
                'User deleted their avatar'
            );
            
            return true;
        }
        
        return false;
    }

    /**
     * Change user password.
     *
     * @param User $user
     * @param string $newPassword
     * @return User
     */
    public function changePassword(User $user, string $newPassword): User
    {
        $user->update(['password' => Hash::make($newPassword)]);
        
        // Log activity
        $this->activityLogService->logActivity(
            'password_changed',
            'User changed their password'
        );
        
        return $user;
    }

    /**
     * Get user profile with activity logs.
     *
     * @param User $user
     * @return array
     */
    public function getProfileWithActivities(User $user): array
    {
        $activities = $this->activityLogService->getUserActivities($user->id, 10);
        
        return [
            'user' => $user,
            'recent_activities' => $activities->items(),
            'avatar_url' => $user->avatar ? Storage::disk('public')->url($user->avatar) : null
        ];
    }
}