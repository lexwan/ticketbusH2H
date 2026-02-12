<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Transaction;
use App\Models\Topup;
use App\Models\TransactionFee;
use App\Models\Mitra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    use ApiResponse;

    public function transactions(Request $request)
    {
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'status' => 'nullable|in:pending,paid,issued,cancelled,failed',
            'mitra_id' => 'nullable|exists:mitra,id',
        ]);

        $query = Transaction::with(['mitra', 'user']);

        if ($request->mitra_id) {
            $query->where('mitra_id', $request->mitra_id);
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $transactions = $query->latest()->paginate(50);

        $summary = [
            'total_transactions' => $query->count(),
            'total_amount' => $query->sum('amount'),
            'by_status' => DB::table('transactions')
                ->selectRaw('status, COUNT(*) as count')
                ->when($request->mitra_id, function($q) use ($request) {
                    $q->where('mitra_id', $request->mitra_id);
                })
                ->groupBy('status')
                ->pluck('count', 'status'),
        ];

        return $this->successResponse('Transaction report retrieved', [
            'summary' => $summary,
            'transactions' => $transactions,
        ]);
    }

    public function topups(Request $request)
    {
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'status' => 'nullable|in:pending,success,rejected',
            'mitra_id' => 'nullable|exists:mitra,id',
        ]);

        $query = Topup::with(['mitra', 'approver']);

        if ($request->mitra_id) {
            $query->where('mitra_id', $request->mitra_id);
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $topups = $query->latest()->paginate(50);

        $summary = [
            'total_topups' => $query->count(),
            'total_amount' => $query->where('status', 'success')->sum('amount'),
            'by_status' => DB::table('topups')
                ->selectRaw('status, COUNT(*) as count')
                ->when($request->mitra_id, function($q) use ($request) {
                    $q->where('mitra_id', $request->mitra_id);
                })
                ->groupBy('status')
                ->pluck('count', 'status'),
        ];

        return $this->successResponse('Topup report retrieved', [
            'summary' => $summary,
            'topups' => $topups,
        ]);
    }

    public function fees(Request $request)
    {
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'mitra_id' => 'nullable|exists:mitra,id',
        ]);

        $query = TransactionFee::with(['mitra', 'transaction']);

        if ($request->mitra_id) {
            $query->where('mitra_id', $request->mitra_id);
        }

        if ($request->date_from) {
            $query->whereHas('transaction', function($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->date_from);
            });
        }
        if ($request->date_to) {
            $query->whereHas('transaction', function($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->date_to);
            });
        }

        $fees = $query->latest('id')->paginate(50);

        $summary = [
            'total_fee' => $query->sum('fee_amount'),
            'by_mitra' => TransactionFee::select('mitra_id', DB::raw('SUM(fee_amount) as total_fee'))
                ->with('mitra:id,name')
                ->when($request->mitra_id, function($q) use ($request) {
                    $q->where('mitra_id', $request->mitra_id);
                })
                ->groupBy('mitra_id')
                ->get()
                ->map(function($item) {
                    return [
                        'mitra_id' => $item->mitra_id,
                        'mitra_name' => $item->mitra->name,
                        'total_fee' => $item->total_fee,
                    ];
                }),
        ];

        return $this->successResponse('Fee report retrieved', [
            'summary' => $summary,
            'fees' => $fees,
        ]);
    }

    public function balances(Request $request)
    {
        $mitras = Mitra::select('id', 'name', 'balance')
            ->withCount('transactions')
            ->get()
            ->map(function($mitra) {
                return [
                    'mitra_id' => $mitra->id,
                    'mitra_name' => $mitra->name,
                    'balance' => $mitra->balance,
                    'total_transactions' => $mitra->transactions_count,
                ];
            });

        $summary = [
            'total_balance' => Mitra::sum('balance'),
            'total_mitra' => Mitra::count(),
        ];

        return $this->successResponse('Balance report retrieved', [
            'summary' => $summary,
            'balances' => $mitras,
        ]);
    }
}
