<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Mitra;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MitraController extends Controller
{
    use ApiResponse;
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string',
            'password' => 'required|string|min:6'
        ]);

        DB::beginTransaction();
        try {
            // Generate kode mitra
            $code = 'MTR' . strtoupper(substr(uniqid(), -6));

            // Buat mitra
            $mitra = Mitra::create([
                'code' => $code,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'status' => 'active',
                'balance' => 0
            ]);

            // Buat user untuk mitra
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'mitra_id' => $mitra->id,
                'status' => 'active'
            ]);
            
            $user->assignRole('mitra');

            DB::commit();

            return $this->successResponse([
                'mitra' => $mitra,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ]
            ], 'Mitra registered successfully', 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to register mitra: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Get All Mitra
     * GET /api/v1/mitra
     */
    public function index(Request $request)
    {
        $mitra = Mitra::with('users')
            ->when($request->status, function($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->paginate(20);

        return $this->successResponse($mitra, 'Mitra retrieved successfully');
    }

    /**
     * Get Mitra Detail
     * GET /api/v1/mitra/{id}
     */
    public function show($id)
    {
        $mitra = Mitra::with('users')->find($id);

        if (!$mitra) {
            return $this->errorResponse('Mitra not found', null, 404);
        }

        return $this->successResponse($mitra, 'Mitra retrieved successfully');
    }

    /**
     * Update Mitra Fee
     * PUT /api/v1/mitra/{id}/fee
     */
    public function updateFee(Request $request, $id)
    {
        $request->validate([
            'type' => 'required|in:percent,flat',
            'value' => 'required|numeric|min:0'
        ]);

        $mitra = Mitra::find($id);
        if (!$mitra) {
            return $this->errorResponse('Mitra not found', null, 404);
        }

        // Update atau create mitra fee
        $mitra->partnerFees()->updateOrCreate(
            ['mitra_id' => $id],
            [
                'type' => $request->type,
                'value' => $request->value,
                'active' => true
            ]
        );

        return $this->successResponse(null, 'Mitra fee updated successfully');
    }
}
