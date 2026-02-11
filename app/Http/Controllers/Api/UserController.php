<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use ApiResponse;

    /**
     * Get All Users
     */
    public function index(Request $request)
    {
        $users = User::with(['roles', 'mitra'])
            ->when($request->status, function($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->when($request->role, function($q) use ($request) {
                $q->whereHas('roles', function($query) use ($request) {
                    $query->where('name', $request->role);
                });
            })
            ->paginate(20);

        return $this->successResponse(
            UserResource::collection($users),
            'Users retrieved successfully'
        );
    }

    /**
     * Buat User
     */
    public function store(StoreUserRequest $request)
    {
        if ($request->role === 'mitra' && !$request->mitra_id) {
            return $this->errorResponse(
                'mitra_id is required for mitra role. Use /mitra/register to create new mitra.',
                null,
                400
            );
        }

        if ($request->role === 'admin' && $request->mitra_id) {
            return $this->errorResponse(
                'Admin cannot have mitra_id',
                null,
                400
            );
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'mitra_id' => $request->mitra_id,
            'status' => $request->status ?? 'active'
        ]);

        $user->assignRole($request->role);

        return $this->successResponse(
            new UserResource($user->load(['roles', 'mitra'])),
            'User created successfully',
            201
        );
    }

    /**
     * Get User Detail
     */
    public function show($id)
    {
        $user = User::with(['roles', 'mitra'])->find($id);

        if (!$user) {
            return $this->errorResponse('User not found', null, 404);
        }

        return $this->successResponse(
            new UserResource($user),
            'User retrieved successfully'
        );
    }

    /**
     * Update User
     */
    public function update(UpdateUserRequest $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->errorResponse('User not found', null, 404);
        }

        // Tidak bisa update diri sendiri
        if ($user->id === auth()->id()) {
            return $this->errorResponse('Cannot update your own account via this endpoint', null, 400);
        }

        // Update data
        $data = $request->only(['name', 'email', 'mitra_id', 'status']);
        
        if ($request->has('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // Update role jika ada
        if ($request->has('role')) {
            $user->syncRoles([$request->role]);
        }

        return $this->successResponse(
            new UserResource($user->load(['roles', 'mitra'])),
            'User updated successfully'
        );
    }

    /**
     * Delete User
     */
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->errorResponse('User not found', null, 404);
        }

        // Tidak bisa hapus diri sendiri
        if ($user->id === auth()->id()) {
            return $this->errorResponse('Cannot delete your own account', null, 400);
        }

        $user->delete();

        return $this->successResponse(null, 'User deleted successfully');
    }
}
