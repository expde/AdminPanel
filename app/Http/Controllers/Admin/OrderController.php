<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('orders')
            ->leftJoin('customers', 'orders.customer_id', '=', 'customers.id')
            ->select('orders.*', 'customers.name as customer_name');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('orders.status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('orders.payment_status', $request->payment_status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('orders.created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('orders.created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('orders.order_number', 'like', "%{$search}%")
                  ->orWhere('orders.customer_name', 'like', "%{$search}%")
                  ->orWhere('orders.customer_email', 'like', "%{$search}%");
            });
        }

        $orders = $query->orderBy('orders.created_at', 'desc')->paginate(20);

        return view('admin.orders.index', compact('orders'));
    }

    public function show($id)
    {
        $order = DB::table('orders')
            ->leftJoin('customers', 'orders.customer_id', '=', 'customers.id')
            ->select('orders.*', 'customers.name as customer_name', 'customers.phone as customer_phone')
            ->where('orders.id', $id)
            ->first();

        if (!$order) {
            return redirect()->route('admin.orders.index')
                ->with('error', 'Order not found.');
        }

        // Get order items
        $orderItems = DB::table('order_items')
            ->leftJoin('products', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('product_images', function($join) {
                $join->on('products.id', '=', 'product_images.product_id')
                     ->where('product_images.is_primary', '=', true);
            })
            ->select('order_items.*', 'products.name as product_name', 'product_images.image_path')
            ->where('order_items.order_id', $id)
            ->get();

        // Get payment history
        $payments = DB::table('payments')
            ->where('order_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.orders.show', compact('order', 'orderItems', 'payments'));
    }

    public function edit($id)
    {
        $order = DB::table('orders')->where('id', $id)->first();

        if (!$order) {
            return redirect()->route('admin.orders.index')
                ->with('error', 'Order not found.');
        }

        return view('admin.orders.edit', compact('order'));
    }

    public function update(Request $request, $id)
    {
        $order = DB::table('orders')->where('id', $id)->first();

        if (!$order) {
            return redirect()->route('admin.orders.index')
                ->with('error', 'Order not found.');
        }

        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled,refunded',
            'payment_status' => 'required|in:pending,paid,failed,refunded',
            'notes' => 'nullable|string',
        ]);

        $updateData = [
            'status' => $request->status,
            'payment_status' => $request->payment_status,
            'notes' => $request->notes,
            'updated_at' => now(),
        ];

        // Update timestamps based on status
        if ($request->status === 'shipped' && $order->status !== 'shipped') {
            $updateData['shipped_at'] = now();
        }

        if ($request->status === 'delivered' && $order->status !== 'delivered') {
            $updateData['delivered_at'] = now();
        }

        DB::table('orders')->where('id', $id)->update($updateData);

        return redirect()->route('admin.orders.show', $id)
            ->with('success', 'Order updated successfully.');
    }

    public function updateStatus(Request $request, $id)
    {
        $order = DB::table('orders')->where('id', $id)->first();

        if (!$order) {
            return response()->json(['error' => 'Order not found.'], 404);
        }

        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled,refunded',
        ]);

        $updateData = [
            'status' => $request->status,
            'updated_at' => now(),
        ];

        // Update timestamps based on status
        if ($request->status === 'shipped' && $order->status !== 'shipped') {
            $updateData['shipped_at'] = now();
        }

        if ($request->status === 'delivered' && $order->status !== 'delivered') {
            $updateData['delivered_at'] = now();
        }

        DB::table('orders')->where('id', $id)->update($updateData);

        return response()->json([
            'success' => true,
            'status' => $request->status,
            'message' => 'Order status updated successfully.'
        ]);
    }

    public function destroy($id)
    {
        $order = DB::table('orders')->where('id', $id)->first();

        if (!$order) {
            return redirect()->route('admin.orders.index')
                ->with('error', 'Order not found.');
        }

        // Delete order items
        DB::table('order_items')->where('order_id', $id)->delete();

        // Delete payments
        DB::table('payments')->where('order_id', $id)->delete();

        // Delete order
        DB::table('orders')->where('id', $id)->delete();

        return redirect()->route('admin.orders.index')
            ->with('success', 'Order deleted successfully.');
    }

    public function print($id)
    {
        $order = DB::table('orders')
            ->leftJoin('customers', 'orders.customer_id', '=', 'customers.id')
            ->select('orders.*', 'customers.name as customer_name', 'customers.phone as customer_phone')
            ->where('orders.id', $id)
            ->first();

        if (!$order) {
            return redirect()->route('admin.orders.index')
                ->with('error', 'Order not found.');
        }

        $orderItems = DB::table('order_items')
            ->leftJoin('products', 'order_items.product_id', '=', 'products.id')
            ->select('order_items.*', 'products.name as product_name')
            ->where('order_items.order_id', $id)
            ->get();

        return view('admin.orders.print', compact('order', 'orderItems'));
    }
}
