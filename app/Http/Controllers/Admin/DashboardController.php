<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        // Today's statistics
        $todayPendingOrders = DB::table('orders')
            ->whereDate('created_at', $today)
            ->where('status', 'pending')
            ->count();

        $todayCompletedOrders = DB::table('orders')
            ->whereDate('created_at', $today)
            ->where('status', 'delivered')
            ->count();

        $todayEarnings = DB::table('orders')
            ->whereDate('created_at', $today)
            ->where('status', 'delivered')
            ->sum('total_amount');

        // Product statistics
        $totalProducts = DB::table('products')->count();
        $lowStockProducts = DB::table('products')
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->count();

        $productsByCategory = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('COUNT(products.id) as count'))
            ->groupBy('categories.id', 'categories.name')
            ->get();

        // Customer statistics
        $totalCustomers = DB::table('customers')->count();
        $newCustomersToday = DB::table('customers')
            ->whereDate('created_at', $today)
            ->count();

        // Top selling products
        $topSellingProducts = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select('products.name', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get();

        // Recent orders
        $recentOrders = DB::table('orders')
            ->leftJoin('customers', 'orders.customer_id', '=', 'customers.id')
            ->select('orders.*', 'customers.name as customer_name')
            ->orderBy('orders.created_at', 'desc')
            ->limit(10)
            ->get();

        // Monthly earnings chart data
        $monthlyEarnings = DB::table('orders')
            ->where('status', 'delivered')
            ->where('created_at', '>=', $thisMonth)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_amount) as earnings'))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        // Order status distribution
        $orderStatusDistribution = DB::table('orders')
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        return view('admin.dashboard', compact(
            'todayPendingOrders',
            'todayCompletedOrders',
            'todayEarnings',
            'totalProducts',
            'lowStockProducts',
            'productsByCategory',
            'totalCustomers',
            'newCustomersToday',
            'topSellingProducts',
            'recentOrders',
            'monthlyEarnings',
            'orderStatusDistribution'
        ));
    }
}
