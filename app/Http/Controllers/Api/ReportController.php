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

    /**
     * report transactions
     */
    public function transactions(Request $request)
    {
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'status' => 'nullable|in:pending,paid,issued,cancelled,failed',
            'mitra_id' => 'nullable|exists:mitra,id',
        ]);

        $query = Transaction::with(['mitra', 'user', 'passengers']);

        // Filter dr role
        if ($request->user()->hasRole('mitra')) {
            $query->where('mitra_id', $request->user()->mitra_id);
        } elseif ($request->mitra_id) {
            $query->where('mitra_id', $request->mitra_id);
        }

        // Filter dr date
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter dr status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $transactions = $query->latest()->paginate(50);
        
        $summary = [
            'total_transactions' => $query->count(),
            'total_amount' => $query->sum('amount'),
            'by_status' => Transaction::selectRaw('status, COUNT(*) as count')
                ->when($request->user()->hasRole('mitra'), function($q) use ($request) {
                    $q->where('mitra_id', $request->user()->mitra_id);
                })
                ->groupBy('status')
                ->pluck('count', 'status'),
        ];

        return $this->successResponse('Transaction report retrieved', [
            'summary' => $summary,
            'transactions' => $transactions,
        ]);
    }

    /**
     * report topups
     */
    public function topups(Request $request)
    {
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'status' => 'nullable|in:pending,success,rejected',
            'mitra_id' => 'nullable|exists:mitra,id',
        ]);

        $query = Topup::with(['mitra', 'approver']);

        // Filter by role
        if ($request->user()->hasRole('mitra')) {
            $query->where('mitra_id', $request->user()->mitra_id);
        } elseif ($request->mitra_id) {
            $query->where('mitra_id', $request->mitra_id);
        }

        // Filter by date
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $topups = $query->latest()->paginate(50);

        // Summary
        $summary = [
            'total_topups' => $query->count(),
            'total_amount' => $query->where('status', 'success')->sum('amount'),
            'by_status' => Topup::selectRaw('status, COUNT(*) as count')
                ->when($request->user()->hasRole('mitra'), function($q) use ($request) {
                    $q->where('mitra_id', $request->user()->mitra_id);
                })
                ->groupBy('status')
                ->pluck('count', 'status'),
        ];

        return $this->successResponse('Topup report retrieved', [
            'summary' => $summary,
            'topups' => $topups,
        ]);
    }

    /**
     * report fees
     */
    public function fees(Request $request)
    {
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'mitra_id' => 'nullable|exists:mitra,id',
        ]);

        $query = TransactionFee::with(['mitra', 'transaction']);

        // Filter by role
        if ($request->user()->hasRole('mitra')) {
            $query->where('mitra_id', $request->user()->mitra_id);
        } elseif ($request->mitra_id) {
            $query->where('mitra_id', $request->mitra_id);
        }

        // Filter by date
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $fees = $query->latest()->paginate(50);

        // Summary
        $summary = [
            'total_fee' => $query->sum('fee_amount'),
        ];

        // By mitra (admin only)
        if ($request->user()->hasRole('admin')) {
            $summary['by_mitra'] = TransactionFee::select('mitra_id', DB::raw('SUM(fee_amount) as total_fee'))
                ->with('mitra:id,name')
                ->groupBy('mitra_id')
                ->get()
                ->map(function($item) {
                    return [
                        'mitra_id' => $item->mitra_id,
                        'mitra_name' => $item->mitra->name,
                        'total_fee' => $item->total_fee,
                    ];
                });
        }

        return $this->successResponse('Fee report retrieved', [
            'summary' => $summary,
            'fees' => $fees,
        ]);
    }

    /**
     * report balances
     */
    public function balances(Request $request)
    {
        if ($request->user()->hasRole('mitra')) {
            // Mitra only see own balance
            $mitra = Mitra::with(['topups', 'transactions'])
                ->find($request->user()->mitra_id);

            return $this->successResponse('Balance report retrieved', [
                'mitra_id' => $mitra->id,
                'mitra_name' => $mitra->name,
                'balance' => $mitra->balance,
                'last_topup' => $mitra->topups()->latest()->first()?->created_at,
                'last_transaction' => $mitra->transactions()->latest()->first()?->created_at,
            ]);
        }

        // Admin see all mitra balances
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

    // Export data untuk PDF (individual)
    public function exportData(Request $request, $type)
    {
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'mitra_id' => 'nullable|exists:mitra,id',
        ]);

        $data = $this->getReportData($request, $type);
        
        return $this->successResponse('Export data retrieved', [
            'type' => $type,
            'data' => $data,
            'generated_at' => now()->toIso8601String()
        ]);
    }

    // Export data untuk PDF (combined)
    public function exportCombinedData(Request $request)
    {
        $request->validate([
            'types' => 'required|array',
            'types.*' => 'in:transactions,topups,fees,balances',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'mitra_id' => 'nullable|exists:mitra,id',
        ]);

        $reports = [];
        foreach ($request->types as $type) {
            $reports[$type] = $this->getReportData($request, $type);
        }
        
        return $this->successResponse('Combined export data retrieved', [
            'reports' => $reports,
            'generated_at' => now()->toIso8601String()
        ]);
    }

    private function getReportData(Request $request, $type)
    {
        return match($type) {
            'transactions' => $this->getTransactionData($request),
            'topups' => $this->getTopupData($request),
            'fees' => $this->getFeeData($request),
            'balances' => $this->getBalanceData($request),
        };
    }

    private function getTransactionData($request)
    {
        $query = Transaction::with(['mitra', 'user', 'passengers']);
        
        if ($request->user()->hasRole('mitra')) {
            $query->where('mitra_id', $request->user()->mitra_id);
        } elseif ($request->mitra_id) {
            $query->where('mitra_id', $request->mitra_id);
        }
        
        if ($request->date_from) $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->date_to) $query->whereDate('created_at', '<=', $request->date_to);
        
        return [
            'items' => $query->latest()->get(),
            'summary' => [
                'total' => $query->count(),
                'amount' => $query->sum('amount'),
            ]
        ];
    }

    private function getTopupData($request)
    {
        $query = Topup::with(['mitra', 'approver']);
        
        if ($request->user()->hasRole('mitra')) {
            $query->where('mitra_id', $request->user()->mitra_id);
        } elseif ($request->mitra_id) {
            $query->where('mitra_id', $request->mitra_id);
        }
        
        if ($request->date_from) $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->date_to) $query->whereDate('created_at', '<=', $request->date_to);
        
        return [
            'items' => $query->latest()->get(),
            'summary' => [
                'total' => $query->count(),
                'amount' => $query->where('status', 'success')->sum('amount'),
            ]
        ];
    }

    private function getFeeData($request)
    {
        $query = TransactionFee::with(['mitra', 'transaction']);
        
        if ($request->user()->hasRole('mitra')) {
            $query->where('mitra_id', $request->user()->mitra_id);
        } elseif ($request->mitra_id) {
            $query->where('mitra_id', $request->mitra_id);
        }
        
        if ($request->date_from) $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->date_to) $query->whereDate('created_at', '<=', $request->date_to);
        
        return [
            'items' => $query->latest()->get(),
            'summary' => ['total_fee' => $query->sum('fee_amount')]
        ];
    }

    private function getBalanceData($request)
    {
        if ($request->user()->hasRole('mitra')) {
            $mitra = Mitra::find($request->user()->mitra_id);
            return ['items' => [$mitra], 'is_single' => true];
        }
        
        return [
            'items' => Mitra::select('id', 'name', 'balance')->get(),
            'summary' => ['total_balance' => Mitra::sum('balance')],
            'is_single' => false
        ];
    }
}