@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="row">
    <!-- Statistics Cards -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stats-number">{{ $todayPendingOrders }}</div>
                    <div class="stats-label">Pending Orders Today</div>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stats-card" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stats-number">{{ $todayCompletedOrders }}</div>
                    <div class="stats-label">Completed Orders Today</div>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stats-card" style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stats-number">${{ number_format($todayEarnings, 2) }}</div>
                    <div class="stats-label">Today's Earnings</div>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stats-card" style="background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%);">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stats-number">{{ $totalProducts }}</div>
                    <div class="stats-label">Total Products</div>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-box"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Low Stock Alert -->
    @if($lowStockProducts > 0)
    <div class="col-12 mb-4">
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Low Stock Alert:</strong> {{ $lowStockProducts }} products are running low on stock.
            <a href="{{ route('admin.products.index') }}" class="alert-link">View Products</a>
        </div>
    </div>
    @endif

    <!-- Charts Row -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Monthly Earnings</h5>
            </div>
            <div class="card-body">
                <canvas id="earningsChart" height="100"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Order Status</h5>
            </div>
            <div class="card-body">
                <canvas id="orderStatusChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Top Selling Products -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>Top Selling Products</h5>
            </div>
            <div class="card-body">
                @if($topSellingProducts->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($topSellingProducts as $product)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">{{ $product->name }}</h6>
                                <small class="text-muted">{{ $product->total_sold }} units sold</small>
                            </div>
                            <span class="badge bg-primary rounded-pill">{{ $product->total_sold }}</span>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted text-center">No sales data available</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Products by Category -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-tags me-2"></i>Products by Category</h5>
            </div>
            <div class="card-body">
                @if($productsByCategory->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($productsByCategory as $category)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">{{ $category->name }}</h6>
                                <small class="text-muted">{{ $category->count }} products</small>
                            </div>
                            <span class="badge bg-info rounded-pill">{{ $category->count }}</span>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted text-center">No category data available</p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Orders -->
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Recent Orders</h5>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                @if($recentOrders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentOrders as $order)
                                <tr>
                                    <td>
                                        <strong>#{{ $order->order_number }}</strong>
                                    </td>
                                    <td>
                                        {{ $order->customer_name ?: $order->customer_name }}
                                        <br>
                                        <small class="text-muted">{{ $order->customer_email }}</small>
                                    </td>
                                    <td>
                                        <strong>${{ number_format($order->total_amount, 2) }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $order->status }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ \Carbon\Carbon::parse($order->created_at)->format('M d, Y') }}
                                        <br>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($order->created_at)->format('h:i A') }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center">No recent orders found</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Monthly Earnings Chart
    const earningsCtx = document.getElementById('earningsChart').getContext('2d');
    const earningsData = @json($monthlyEarnings);
    
    const earningsChart = new Chart(earningsCtx, {
        type: 'line',
        data: {
            labels: earningsData.map(item => new Date(item.date).toLocaleDateString()),
            datasets: [{
                label: 'Earnings ($)',
                data: earningsData.map(item => item.earnings),
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toFixed(2);
                        }
                    }
                }
            }
        }
    });

    // Order Status Chart
    const statusCtx = document.getElementById('orderStatusChart').getContext('2d');
    const statusData = @json($orderStatusDistribution);
    
    const orderStatusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: statusData.map(item => item.status.charAt(0).toUpperCase() + item.status.slice(1)),
            datasets: [{
                data: statusData.map(item => item.count),
                backgroundColor: [
                    '#ffc107',
                    '#17a2b8',
                    '#28a745',
                    '#dc3545',
                    '#6c757d',
                    '#6f42c1'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
</script>
@endsection
