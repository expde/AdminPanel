<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('payments')
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->leftJoin('customers', 'orders.customer_id', '=', 'customers.id')
            ->select('payments.*', 'orders.order_number', 'orders.total_amount as order_total', 'customers.name as customer_name');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('payments.status', $request->status);
        }

        if ($request->filled('payment_method')) {
            $query->where('payments.payment_method', $request->payment_method);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('payments.created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('payments.created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('orders.order_number', 'like', "%{$search}%")
                  ->orWhere('payments.payment_reference', 'like', "%{$search}%")
                  ->orWhere('payments.transaction_id', 'like', "%{$search}%");
            });
        }

        $payments = $query->orderBy('payments.created_at', 'desc')->paginate(20);

        return view('admin.payments.index', compact('payments'));
    }

    public function reports(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth());
        $dateTo = $request->get('date_to', Carbon::now()->endOfMonth());

        // Daily earnings
        $dailyEarnings = DB::table('payments')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        // Payment method breakdown
        $paymentMethods = DB::table('payments')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('payment_method')
            ->get();

        // Monthly comparison
        $currentMonth = DB::table('payments')
            ->where('status', 'completed')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('amount');

        $lastMonth = DB::table('payments')
            ->where('status', 'completed')
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->whereYear('created_at', Carbon::now()->subMonth()->year)
            ->sum('amount');

        // Total statistics
        $totalEarnings = DB::table('payments')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->sum('amount');

        $totalTransactions = DB::table('payments')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->count();

        $successfulTransactions = DB::table('payments')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->count();

        $successRate = $totalTransactions > 0 ? ($successfulTransactions / $totalTransactions) * 100 : 0;

        return view('admin.payments.reports', compact(
            'dailyEarnings',
            'paymentMethods',
            'currentMonth',
            'lastMonth',
            'totalEarnings',
            'totalTransactions',
            'successfulTransactions',
            'successRate',
            'dateFrom',
            'dateTo'
        ));
    }
}
