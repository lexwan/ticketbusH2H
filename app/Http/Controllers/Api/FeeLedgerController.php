<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\PartnerFeeLedger;
use Illuminate\Http\Request;

class FeeLedgerController extends Controller
{
    use ApiResponse;

    /**
     * Get Fee Ledger
     */
    public function index(Request $request)
    {
        $query = PartnerFeeLedger::with(['mitra','transaction']);

        if ($request->user()->hasRole('admin')) {
            if ($request->mitra_id) {
                $query->where('mitra_id', $request->mitra_id);
            }
        } else {
            $query->where('mitra_id', $request->user()->mitra_id);
        }

        $ledgers = $query->latest()->paginate(15);

        return $this->successResponse('Fee ledgers retrieved successfully', $ledgers);
    }
}
