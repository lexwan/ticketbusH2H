<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Mitra;
use App\Models\TopupHistory;
use Illuminate\Http\Request;

class BalanceController extends Controller
{
    use ApiResponse;

    /**
     * Get Balance
     */
    public function index(Request $request)
    {
        if ($request->user()->hasRole('admin')) {
            $balances = Mitra::select('id', 'code', 'name', 'balance')->get();
            return $this->successResponse('Balances retrieved successfully', $balances);
        }

        $mitra = Mitra::find($request->user()->mitra_id);

        if (!$mitra) {
            return $this->errorResponse('Mitra not found', null, 404);
        }

        return $this->successResponse('Balance retrieved successfully', [
            'mitra_id' => $mitra->id,
            'mitra_name' => $mitra->name,
            'balance' => $mitra->balance,
        ]);
    }

    /**
     * Get Balance History
     */
    public function histories(Request $request)
    {
        $query = TopupHistory::with(['mitra', 'topup']);

        //admin, can filter by mitra_id
        if ($request->user()->hasRole('admin')) {
            if ($request->mitra_id) {
                $query->where('mitra_id', $request->mitra_id);
            }
        } else {
            //mitra, only see their own history
            $query->where('mitra_id', $request->user()->mitra_id);
        }

        $histories = $query->latest()->paginate(15);

        return $this->succesResponse('Balance histories retrieved successfully', $histories);
    }
}