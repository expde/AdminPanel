<?php
// Admin Panel Entry Point
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Get user info
$user_name = $_SESSION['admin_name'] ?? 'Admin';
$user_role = $_SESSION['admin_role'] ?? 'admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#10b981" />
    <meta name="description" content="Expde Shop Admin Panel - Manage your e-commerce store with ease. Dashboard, products, orders, customers, and more.">
    <meta name="keywords" content="admin panel, e-commerce, shop management, dashboard, orders, products, customers">
    <meta name="author" content="Expde Shop">
    <meta name="robots" content="noindex, nofollow">
    <title>Dashboard | Expde Shop Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            color: #334155;
            line-height: 1.6;
        }
        
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: white;
            border-right: 1px solid #e2e8f0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
        }
        
        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .logo i {
            font-size: 1.5rem;
        }
        
        .logo h3 {
            font-size: 1.25rem;
            font-weight: 700;
            margin: 0;
        }
        
        .logo p {
            font-size: 0.875rem;
            opacity: 0.9;
            margin: 0;
        }
        
        .sidebar-nav {
            padding: 1rem 0;
        }
        
        .nav-item {
            margin: 0.25rem 1rem;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: #64748b;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s ease;
            font-weight: 500;
        }
        
        .nav-link:hover {
            background: #f1f5f9;
            color: #1e293b;
        }
        
        .nav-link.active {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }
        
        .nav-link i {
            width: 20px;
            text-align: center;
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            background: #f8fafc;
        }
        
        .header {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .sidebar-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.25rem;
            color: #64748b;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 6px;
            transition: all 0.2s ease;
        }
        
        .sidebar-toggle:hover {
            background: #f1f5f9;
            color: #1e293b;
        }
        
        .header-left h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .header-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .search-box {
            position: relative;
        }
        
        .search-box input {
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            background: #f8fafc;
            width: 300px;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }
        
        .search-box input:focus {
            outline: none;
            border-color: #10b981;
            background: white;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }
        
        .search-box i {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: #f1f5f9;
            border-radius: 8px;
            cursor: pointer;
        }
        
        .user-avatar {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }
        
        /* Dashboard Content */
        .dashboard-content {
            padding: 2rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #10b981, #059669);
        }
        
        .stat-card.pending::before {
            background: linear-gradient(90deg, #f59e0b, #d97706);
        }
        
        .stat-card.completed::before {
            background: linear-gradient(90deg, #8b5cf6, #7c3aed);
        }
        
        .stat-card.earnings::before {
            background: linear-gradient(90deg, #10b981, #059669);
        }
        
        .stat-card.products::before {
            background: linear-gradient(90deg, #8b5cf6, #7c3aed);
        }
        
        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .stat-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }
        
        .stat-icon.pending {
            background: #fef3c7;
            color: #d97706;
        }
        
        .stat-icon.completed {
            background: #ede9fe;
            color: #7c3aed;
        }
        
        .stat-icon.earnings {
            background: #d1fae5;
            color: #059669;
        }
        
        .stat-icon.products {
            background: #f3e8ff;
            color: #8b5cf6;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }
        
        .stat-change {
            font-size: 0.875rem;
            color: #10b981;
            font-weight: 500;
        }
        
        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }
        
        .content-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }
        
        .card-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }
        
        .recent-badge {
            background: #d1fae5;
            color: #059669;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .card-body {
            padding: 0;
        }
        
        .order-item {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.2s ease;
        }
        
        .order-item:hover {
            background: #f8fafc;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .order-info {
            flex: 1;
        }
        
        .order-name {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }
        
        .order-amount {
            font-size: 1.125rem;
            font-weight: 700;
            color: #059669;
            margin-bottom: 0.25rem;
        }
        
        .order-phone {
            font-size: 0.875rem;
            color: #64748b;
        }
        
        .order-status {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.5rem;
        }
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .status-badge.pending {
            background: #fef3c7;
            color: #d97706;
        }
        
        .status-badge.completed {
            background: #ede9fe;
            color: #7c3aed;
        }
        
        .order-date {
            font-size: 0.75rem;
            color: #9ca3af;
        }
        
        .status-icon {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
        }
        
        .status-icon.pending {
            background: #fef3c7;
            color: #d97706;
        }
        
        .status-icon.completed {
            background: #ede9fe;
            color: #7c3aed;
        }
        
        /* Submenu Styles */
        .nav-group {
            margin-bottom: 0.5rem;
        }
        
        .nav-toggle {
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 1.5rem;
            color: #64748b;
            transition: all 0.2s ease;
            border-radius: 8px;
            margin: 0.25rem 0.75rem;
        }
        
        .nav-toggle span {
            flex: 1;
            text-align: left;
            margin-left: 0.75rem;
        }
        
        .nav-toggle:hover {
            background: #f1f5f9;
            color: #1e293b;
        }
        
        .nav-toggle.active {
            background: #d1fae5;
            color: #059669;
        }
        
        .nav-arrow {
            font-size: 0.75rem;
            transition: transform 0.3s ease;
        }
        
        .nav-toggle.active .nav-arrow {
            transform: rotate(180deg);
        }
        
        .nav-submenu {
            max-height: 0;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: #f8fafc;
            border-radius: 8px;
            margin: 0.25rem 0.75rem;
            border: 1px solid #e2e8f0;
            opacity: 0;
            transform: translateY(-10px);
        }
        
        .nav-submenu.active {
            max-height: 300px;
            padding: 0.5rem 0;
            opacity: 1;
            transform: translateY(0);
        }
        
        .nav-subitem {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            color: #64748b;
            text-decoration: none;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            border-radius: 6px;
            margin: 0.125rem 0.5rem;
        }
        
        .nav-subitem:hover {
            background: #e2e8f0;
            color: #1e293b;
            transform: translateX(4px);
        }
        
        .nav-subitem i {
            font-size: 0.75rem;
            width: 16px;
            text-align: center;
        }
        
        .nav-subitem span {
            flex: 1;
            text-align: left;
        }
        
        /* Button Styles */
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 0.875rem;
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid #10b981;
            color: #10b981;
            width: 160px;
            height: 44px;
            justify-content: center;
            white-space: nowrap;
            font-size: 0.8rem;
        }
        
        .btn-outline:hover {
            background: #10b981;
            border-color: #10b981;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
            width: 160px;
            height: 44px;
            justify-content: center;
            white-space: nowrap;
            font-size: 0.8rem;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar-toggle {
                display: block;
            }
            
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .sidebar.open {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 999;
                display: none;
            }
            
            .sidebar-overlay.active {
                display: block;
            }
            
            .content-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }
            
            .header {
                padding: 1rem;
                flex-direction: row;
                gap: 1rem;
                align-items: center;
            }
            
            .header-left {
                flex: 1;
                min-width: 0;
            }
            
            .header-left h1 {
                font-size: 1.25rem;
                margin: 0;
            }
            
            .header-right {
                flex: 1;
                justify-content: flex-end;
                gap: 0.75rem;
            }
            
            .search-box {
                flex: 1;
                max-width: 200px;
            }
            
            .header-actions {
                gap: 0.5rem;
                flex-shrink: 0;
            }
            
            .btn {
                padding: 0.75rem 1rem;
                font-size: 0.75rem;
                width: 140px;
                height: 42px;
                white-space: nowrap;
            }
            
            .search-box input {
                width: 200px;
            }
            
            .dashboard-content {
                padding: 1rem;
            }
        }
        
        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .header {
                padding: 0.75rem 1rem;
                flex-direction: column;
                gap: 0.75rem;
                align-items: stretch;
            }
            
            .header-left {
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }
            
            .header-left h1 {
                font-size: 1.125rem;
            }
            
            .header-right {
                flex-direction: column;
                gap: 0.75rem;
                align-items: stretch;
            }
            
            .search-box {
                max-width: none;
            }
            
            .search-box input {
                width: 100%;
            }
            
            .header-actions {
                display: flex;
                gap: 0.5rem;
                justify-content: space-between;
            }
            
            .btn {
                flex: 1;
                padding: 0.75rem 1rem;
                font-size: 0.875rem;
                text-align: center;
                width: auto;
                height: 44px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar Overlay -->
        <div class="sidebar-overlay" onclick="closeSidebar()"></div>
        
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-shopping-cart"></i>
                    <div>
                        <h3>Expde Shop</h3>
                        <p>Admin Panel</p>
                    </div>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-item">
                    <a class="nav-link active" href="index.php">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </div>
                
                <div class="nav-group">
                    <div class="nav-item nav-toggle" onclick="toggleSubmenu('products')">
                        <i class="fas fa-box"></i>
                        <span>Products</span>
                        <i class="fas fa-chevron-down nav-arrow"></i>
                    </div>
                    <div class="nav-submenu" id="products-submenu">
                        <a href="products.php" class="nav-subitem">
                            <i class="fas fa-list"></i>
                            <span>All Products</span>
                        </a>
                        <a href="products.php?action=add" class="nav-subitem">
                            <i class="fas fa-plus"></i>
                            <span>Add Product</span>
                        </a>
                        <a href="products.php?action=import" class="nav-subitem">
                            <i class="fas fa-upload"></i>
                            <span>Import Products</span>
                        </a>
                    </div>
                </div>
                
                <div class="nav-group">
                    <div class="nav-item nav-toggle" onclick="toggleSubmenu('categories')">
                        <i class="fas fa-tags"></i>
                        <span>Categories</span>
                        <i class="fas fa-chevron-down nav-arrow"></i>
                    </div>
                    <div class="nav-submenu" id="categories-submenu">
                        <a href="categories.php" class="nav-subitem">
                            <i class="fas fa-list"></i>
                            <span>All Categories</span>
                        </a>
                        <a href="categories.php?action=add" class="nav-subitem">
                            <i class="fas fa-plus"></i>
                            <span>Add Category</span>
                        </a>
                    </div>
                </div>
                
                <div class="nav-group">
                    <div class="nav-item nav-toggle" onclick="toggleSubmenu('orders')">
                        <i class="fas fa-shopping-bag"></i>
                        <span>Orders</span>
                        <i class="fas fa-chevron-down nav-arrow"></i>
                    </div>
                    <div class="nav-submenu" id="orders-submenu">
                        <a href="orders.php" class="nav-subitem">
                            <i class="fas fa-list"></i>
                            <span>All Orders</span>
                        </a>
                        <a href="orders.php?status=pending" class="nav-subitem">
                            <i class="fas fa-clock"></i>
                            <span>Pending Orders</span>
                        </a>
                        <a href="orders.php?status=completed" class="nav-subitem">
                            <i class="fas fa-check"></i>
                            <span>Completed Orders</span>
                        </a>
                    </div>
                </div>
                
                <div class="nav-group">
                    <div class="nav-item nav-toggle" onclick="toggleSubmenu('customers')">
                        <i class="fas fa-users"></i>
                        <span>Customers</span>
                        <i class="fas fa-chevron-down nav-arrow"></i>
                    </div>
                    <div class="nav-submenu" id="customers-submenu">
                        <a href="customers.php" class="nav-subitem">
                            <i class="fas fa-list"></i>
                            <span>All Customers</span>
                        </a>
                        <a href="customers.php?action=add" class="nav-subitem">
                            <i class="fas fa-user-plus"></i>
                            <span>Add Customer</span>
                        </a>
                    </div>
                </div>
                
                <div class="nav-group">
                    <div class="nav-item nav-toggle" onclick="toggleSubmenu('payments')">
                        <i class="fas fa-credit-card"></i>
                        <span>Payments</span>
                        <i class="fas fa-chevron-down nav-arrow"></i>
                    </div>
                    <div class="nav-submenu" id="payments-submenu">
                        <a href="payments.php" class="nav-subitem">
                            <i class="fas fa-list"></i>
                            <span>Payment History</span>
                        </a>
                        <a href="payments.php?action=reports" class="nav-subitem">
                            <i class="fas fa-chart-line"></i>
                            <span>Payment Reports</span>
                        </a>
                    </div>
                </div>
                
                <div class="nav-group">
                    <div class="nav-item nav-toggle" onclick="toggleSubmenu('settings')">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                        <i class="fas fa-chevron-down nav-arrow"></i>
                    </div>
                    <div class="nav-submenu" id="settings-submenu">
                        <a href="settings.php" class="nav-subitem">
                            <i class="fas fa-store"></i>
                            <span>Store Settings</span>
                        </a>
                        <a href="settings.php?tab=profile" class="nav-subitem">
                            <i class="fas fa-user-cog"></i>
                            <span>Profile Settings</span>
                        </a>
                        <a href="settings.php?tab=users" class="nav-subitem">
                            <i class="fas fa-users-cog"></i>
                            <span>User Management</span>
                        </a>
                    </div>
                </div>
                
                <div class="nav-item">
                    <a class="nav-link logout" href="logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </div>
            </nav>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <button class="sidebar-toggle" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Dashboard</h1>
            </div>
            <div class="header-right">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search orders...">
                </div>
                    <div class="header-actions">
                        <button class="btn btn-outline" onclick="window.location.href='products.php?action=add'">
                            <i class="fas fa-plus"></i>
                            Add Product
                        </button>
                        <button class="btn btn-primary" onclick="window.location.href='orders.php'">
                            <i class="fas fa-shopping-cart"></i>
                            Check Order
                        </button>
                    </div>
            </div>
        </div>
            
            <!-- Dashboard Content -->
            <div class="dashboard-content">
                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card pending">
                        <div class="stat-header">
                            <div class="stat-title">Today Pending</div>
                            <div class="stat-icon pending">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                        </div>
                        <div class="stat-value">0</div>
                        <div class="stat-change">No orders yet</div>
                    </div>
                    
                    <div class="stat-card completed">
                        <div class="stat-header">
                            <div class="stat-title">Today Completed</div>
                            <div class="stat-icon completed">
                                <i class="fas fa-box"></i>
                            </div>
                        </div>
                        <div class="stat-value">0</div>
                        <div class="stat-change">No orders yet</div>
                    </div>
                    
                    <div class="stat-card earnings">
                        <div class="stat-header">
                            <div class="stat-title">Today Earnings</div>
                            <div class="stat-icon earnings">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                        <div class="stat-value">$0</div>
                        <div class="stat-change">No earnings yet</div>
                    </div>
                    
                    <div class="stat-card products">
                        <div class="stat-header">
                            <div class="stat-title">Total Products</div>
                            <div class="stat-icon products">
                                <i class="fas fa-box"></i>
                            </div>
                        </div>
                        <div class="stat-value">0</div>
                        <div class="stat-change">No products yet</div>
                    </div>
                </div>
                
                <!-- Content Grid -->
                <div class="content-grid">
                    <!-- Pending Orders -->
                    <div class="content-card">
                        <div class="card-header">
                            <h3 class="card-title">Pending Orders Recent</h3>
                            <span class="recent-badge">Recent</span>
                        </div>
                        <div class="card-body">
                            <div class="text-center py-4">
                                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No pending orders</h5>
                                <p class="text-muted">Pending orders will appear here once customers place them.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Completed Orders -->
                    <div class="content-card">
                        <div class="card-header">
                            <h3 class="card-title">Completed Orders Recent</h3>
                            <span class="recent-badge">Recent</span>
                        </div>
                        <div class="card-body">
                            <div class="text-center py-4">
                                <i class="fas fa-box fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No completed orders</h5>
                                <p class="text-muted">Completed orders will appear here once orders are processed.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Sidebar toggle functionality
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
        }
        
        function closeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
        }
        
        // Submenu toggle functionality
        function toggleSubmenu(menuId) {
            const submenu = document.getElementById(menuId + '-submenu');
            const toggle = submenu.previousElementSibling;
            
            // Close all other submenus
            document.querySelectorAll('.nav-submenu').forEach(menu => {
                if (menu.id !== menuId + '-submenu') {
                    menu.classList.remove('active');
                    menu.previousElementSibling.classList.remove('active');
                }
            });
            
            // Toggle current submenu
            submenu.classList.toggle('active');
            toggle.classList.toggle('active');
        }
        
        // Close sidebar when clicking on a link (mobile)
        document.querySelectorAll('.nav-link, .nav-subitem').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    closeSidebar();
                }
            });
        });
        
        // Close sidebar on window resize if desktop
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                closeSidebar();
            }
        });
    </script>
</body>
</html>
