<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('customers');

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $customers = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.customers.index', compact('customers'));
    }

    public function show($id)
    {
        $customer = DB::table('customers')->where('id', $id)->first();

        if (!$customer) {
            return redirect()->route('admin.customers.index')
                ->with('error', 'Customer not found.');
        }

        // Get customer orders
        $orders = DB::table('orders')
            ->where('customer_id', $id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Get order statistics
        $orderStats = DB::table('orders')
            ->where('customer_id', $id)
            ->selectRaw('
                COUNT(*) as total_orders,
                SUM(total_amount) as total_spent,
                AVG(total_amount) as average_order_value,
                MAX(created_at) as last_order_date
            ')
            ->first();

        return view('admin.customers.show', compact('customer', 'orders', 'orderStats'));
    }

    public function edit($id)
    {
        $customer = DB::table('customers')->where('id', $id)->first();

        if (!$customer) {
            return redirect()->route('admin.customers.index')
                ->with('error', 'Customer not found.');
        }

        return view('admin.customers.edit', compact('customer'));
    }

    public function update(Request $request, $id)
    {
        $customer = DB::table('customers')->where('id', $id)->first();

        if (!$customer) {
            return redirect()->route('admin.customers.index')
                ->with('error', 'Customer not found.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'is_active' => 'boolean',
        ]);

        DB::table('customers')->where('id', $id)->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            'postal_code' => $request->postal_code,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'is_active' => $request->has('is_active'),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.customers.show', $id)
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy($id)
    {
        $customer = DB::table('customers')->where('id', $id)->first();

        if (!$customer) {
            return redirect()->route('admin.customers.index')
                ->with('error', 'Customer not found.');
        }

        // Check if customer has orders
        $orderCount = DB::table('orders')->where('customer_id', $id)->count();
        if ($orderCount > 0) {
            return redirect()->route('admin.customers.index')
                ->with('error', 'Cannot delete customer with orders. Please delete orders first.');
        }

        DB::table('customers')->where('id', $id)->delete();

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer deleted successfully.');
    }

    public function toggleStatus($id)
    {
        $customer = DB::table('customers')->where('id', $id)->first();

        if (!$customer) {
            return response()->json(['error' => 'Customer not found.'], 404);
        }

        $newStatus = !$customer->is_active;
        DB::table('customers')->where('id', $id)->update([
            'is_active' => $newStatus,
            'updated_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'is_active' => $newStatus,
            'message' => 'Customer status updated successfully.'
        ]);
    }
}
