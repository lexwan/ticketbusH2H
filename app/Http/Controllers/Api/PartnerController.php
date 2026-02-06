<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Mitra;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PartnerController extends Controller
{
    use ApiResponse;

    /**
     * Admin: Register Mitra Baru
     * POST /api/v1/partners/register
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:mitra,email',
            'phone' => 'required|string',
            'user_name' => 'required|string',
            'user_email' => 'required|email|unique:users,email',
            'user_password' => 'required|string|min:6'
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

            // Ambil role mitra
            $mitraRole = Role::where('name', 'mitra')->first();

            // Buat user untuk mitra
            $user = User::create([
                'name' => $request->user_name,
                'email' => $request->user_email,
                'password' => Hash::make($request->user_password),
                'role_id' => $mitraRole->id,
                'mitra_id' => $mitra->id,
                'status' => 'active'
            ]);

            DB::commit();

            return $this->successResponse([
                'mitra' => $mitra,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ]
            ], 'Partner registered successfully', 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to register partner: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Get All Partners
     * GET /api/v1/partners
     */
    public function index(Request $request)
    {
        $partners = Mitra::with('users')
            ->when($request->status, function($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->paginate(20);

        return $this->successResponse($partners, 'Partners retrieved successfully');
    }

    /**
     * Get Partner Detail
     * GET /api/v1/partners/{id}
     */
    public function show($id)
    {
        $partner = Mitra::with('users')->find($id);

        if (!$partner) {
            return $this->errorResponse('Partner not found', null, 404);
        }

        return $this->successResponse($partner, 'Partner retrieved successfully');
    }

    /**
     * Update Partner Fee
     * PUT /api/v1/partners/{id}/fee
     */
    public function updateFee(Request $request, $id)
    {
        $request->validate([
            'type' => 'required|in:percent,flat',
            'value' => 'required|numeric|min:0'
        ]);

        $partner = Mitra::find($id);
        if (!$partner) {
            return $this->errorResponse('Partner not found', null, 404);
        }

        // Update atau create partner fee
        $partner->partnerFees()->updateOrCreate(
            ['mitra_id' => $id],
            [
                'type' => $request->type,
                'value' => $request->value,
                'active' => true
            ]
        );

        return $this->successResponse(null, 'Partner fee updated successfully');
    }
}
