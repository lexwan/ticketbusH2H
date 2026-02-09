<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\User;
use App\Models\Mitra;
use App\Models\Transaction;
use App\Models\Topup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use ApiResponse;

    /**
     * Dashboard Admin
     */
    public function admin(Request $request)
    {
        $data = [
            'total_mitra' => Mitra::count(),
            'total_mitra_active' => Mitra::where('status', 'active')->count(),
            'total_mitra_pending' => Mitra::where('status', 'pending')->count(),
            
            'total_users' => User::count(),
            
            'total_transactions' => Transaction::count(),
            'total_transactions_today' => Transaction::whereDate('created_at', today())->count(),
            'total_revenue' => Transaction::where('status', 'issued')->sum('amount'),
            'revenue_today' => Transaction::where('status', 'issued')
                ->whereDate('created_at', today())
                ->sum('amount'),
            
            'total_topups_pending' => Topup::where('status', 'pending')->count(),
            'total_topups_today' => Topup::whereDate('created_at', today())->count(),
            
            'recent_transactions' => Transaction::with(['mitra', 'user'])
                ->latest()
                ->limit(10)
                ->get(),
            
            'recent_topups' => Topup::with('mitra')
                ->latest()
                ->limit(10)
                ->get(),
        ];

        return $this->successResponse($data, 'Admin dashboard data retrieved');
    }

    /**
     * Dashboard Mitra
     */
    public function mitra(Request $request)
    {
        $user = $request->user();
        $mitra = $user->mitra;

        if (!$mitra) {
            return $this->errorResponse('Mitra not found', null, 404);
        }

        $data = [
            'mitra_info' => [
                'code' => $mitra->code,
                'name' => $mitra->name,
                'email' => $mitra->email,
                'phone' => $mitra->phone,
                'status' => $mitra->status,
                'balance' => $mitra->balance,
            ],
            
            'total_transactions' => Transaction::where('mitra_id', $mitra->id)->count(),
            'total_transactions_today' => Transaction::where('mitra_id', $mitra->id)
                ->whereDate('created_at', today())
                ->count(),
            
            'total_spent' => Transaction::where('mitra_id', $mitra->id)
                ->where('status', 'issued')
                ->sum('amount'),
            'spent_today' => Transaction::where('mitra_id', $mitra->id)
                ->where('status', 'issued')
                ->whereDate('created_at', today())
                ->sum('amount'),
            
            'transactions_by_status' => Transaction::where('mitra_id', $mitra->id)
                ->select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->get(),
            
            'recent_transactions' => Transaction::where('mitra_id', $mitra->id)
                ->with('passengers')
                ->latest()
                ->limit(10)
                ->get(),
            
            'recent_topups' => Topup::where('mitra_id', $mitra->id)
                ->latest()
                ->limit(5)
                ->get(),
        ];

        return $this->successResponse($data, 'Mitra dashboard data retrieved');
    }
}
