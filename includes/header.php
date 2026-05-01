<?php
session_start();

// Determine the correct path to config/db.php based on where header.php is being included from
if (file_exists('config/db.php')) {
    require_once 'config/db.php';
} elseif (file_exists('../config/db.php')) {
    require_once '../config/db.php';
} else {
    die("Database configuration file not found. Please check the file path.");
}

// Get user role and name (default for demo)
$role = $_SESSION['role'] ?? 'admin';
$user_full_name = $_SESSION['full_name'] ?? 'Administrator';

// Determine current page for active menu
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        :root {
            --primary-green: #1e6b3e;
            --primary-green-dark: #14492a;
            --primary-green-light: #2f7a4d;
            --sidebar-width: 280px;
        }

        body {
            background: #f8f9fa;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            overflow-x: hidden;
            padding-top: 70px;
        }

        /* Top Navigation Bar */
        .navbar-top {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            padding: 0.75rem 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            z-index: 1040;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
        }

        .navbar-brand {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary-green) !important;
            letter-spacing: -0.5px;
            text-decoration: none;
            margin-left: 270px
        
        }

        .navbar-brand i {
            color: var(--primary-green);
        }

                .user-badge {
            background: #f0fdf4;
            color: var(--primary-green);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        
        .user-badge:hover {
            background: #dcfce7;
            color: var(--primary-green-dark);
        }

        .status-indicator {
            width: 8px;
            height: 8px;
            background: #22c55e;
            border-radius: 50%;
            display: inline-block;
            margin-right: 0.5rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, var(--primary-green) 0%, var(--primary-green-dark) 100%);
            z-index: 1050;
            padding-top: 80px;
            transition: transform 0.3s ease;
            overflow-y: auto;
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.875rem 1.5rem;
            margin: 0.25rem 1rem;
            border-radius: 12px;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
        }

        .sidebar .nav-link i {
            font-size: 1.25rem;
            width: 24px;
            text-align: center;
        }

        .sidebar .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            transform: translateX(5px);
        }

        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            font-weight: 600;
        }

        .sidebar-divider {
            border-top: 1px solid rgba(255,255,255,0.15);
            margin: 1rem 1.5rem;
        }

        .sidebar-label {
            color: rgba(255,255,255,0.5);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 0.5rem 1.5rem;
            font-weight: 600;
            margin-top: 0.5rem;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            min-height: 100vh;
        }

        /* Cards */
        .card {
            border-radius: 16px;
            border: none;
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 3rem rgba(0,0,0,0.1) !important;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary-green);
            position: relative;
        }

        .stat-card:hover {
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        /* Buttons */
        .btn-primary {
            background: var(--primary-green);
            border-color: var(--primary-green);
            border-radius: 12px;
            padding: 0.625rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: var(--primary-green-dark);
            border-color: var(--primary-green-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(30, 107, 62, 0.3);
        }

        .btn-outline-primary {
            color: var(--primary-green);
            border-color: var(--primary-green);
            border-radius: 12px;
            font-weight: 600;
        }

        .btn-outline-primary:hover {
            background: var(--primary-green);
            border-color: var(--primary-green);
            color: white;
        }

        /* Tables */
        .table {
            border-radius: 12px;
            overflow: hidden;
        }

        .table thead th {
            background: #f8f9fa;
            border-bottom: 2px solid #e5e7eb;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
            font-weight: 700;
            padding: 1rem;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #f3f4f6;
        }

        .table tbody tr:hover {
            background: #f9fafb;
        }

        /* Badges */
        .badge {
            padding: 0.5rem 0.75rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.75rem;
            letter-spacing: 0.3px;
        }

        /* Modals */
        .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        }

        .modal-header {
            border-bottom: 1px solid #e5e7eb;
            padding: 1.5rem;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            border-top: 1px solid #e5e7eb;
            padding: 1.25rem 1.5rem;
        }

        /* Form Controls */
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e5e7eb;
            padding: 0.625rem 1rem;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-green);
            box-shadow: 0 0 0 0.2rem rgba(30, 107, 62, 0.15);
        }

        .form-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        /* Progress Bars */
        .progress {
            height: 8px;
            border-radius: 50px;
            background: #f3f4f6;
        }

        .progress-bar {
            border-radius: 50px;
            background: linear-gradient(90deg, var(--primary-green) 0%, var(--primary-green-light) 100%);
        }

        /* Toast Notifications */
        .toast {
            border-radius: 16px;
            border: none;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }

        /* Page Headers */
        .page-header {
            margin-bottom: 2rem;
        }

        .page-header h1 {
            font-size: 2rem;
            font-weight: 800;
            color: #111827;
            letter-spacing: -0.5px;
        }

        .page-header p {
            color: #6b7280;
            font-size: 0.95rem;
        }

        /* Filter Bar */
        .filter-bar {
            background: white;
            padding: 1.25rem;
            border-radius: 16px;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        /* Pagination */
        .pagination .page-link {
            border-radius: 10px;
            margin: 0 2px;
            border: none;
            color: var(--primary-green);
            font-weight: 500;
            padding: 0.5rem 1rem;
        }

        .pagination .page-item.active .page-link {
            background: var(--primary-green);
            color: white;
        }

        /* Dashboard Specific */
        .welcome-card {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--primary-green-dark) 100%);
            color: white;
            border-radius: 24px;
            padding: 2.5rem;
            margin-bottom: 2rem;
        }

        .welcome-card h1 {
            font-size: 2.5rem;
            font-weight: 800;
            letter-spacing: -1px;
        }

        .module-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            border-left: 5px solid var(--primary-green);
            height: 100%;
        }

        .module-card:hover {
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            transform: translateY(-5px);
        }

        .module-card .icon-circle {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            background: #f0fdf4;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .module-card .icon-circle i {
            font-size: 1.75rem;
            color: var(--primary-green);
        }

        /* Custom Scrollbar */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.2);
            border-radius: 3px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
            .welcome-card {
                padding: 1.5rem;
            }
            .welcome-card h1 {
                font-size: 1.75rem;
            }
        }
                .user-badge.dropdown-toggle::after {
            margin-left: 0.5rem;
            vertical-align: middle;
        }
        
        .dropdown-item {
            font-size: 0.85rem;
            transition: all 0.2s ease;
        }
        
        .dropdown-item:hover {
            background-color: #f0fdf4;
            color: var(--primary-green);
        }
        
        .dropdown-item.text-danger:hover {
            background-color: #fef2f2;
            color: #dc3545 !important;
        }
    </style>
</head>
<body>

        <!-- Top Navigation -->
    <nav class="navbar navbar-top">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-bank2 me-2"></i>Finance Management System
            </a>
            <div class="d-flex align-items-center gap-3">
                <span class="status-indicator"></span>
                <span class="text-muted small fw-semibold text-uppercase d-none d-md-inline" style="font-size: 0.65rem;">System Operational</span>
                
                <!-- User Dropdown -->
                <div class="dropdown">
                    <button class="user-badge dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="border: none; cursor: pointer;">
                        <i class="bi bi-person-circle"></i>
                        <span><?php echo htmlspecialchars($user_full_name); ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="border-radius: 12px; border: none; margin-top: 10px; min-width: 200px;">
                        <li class="px-3 py-2">
                            <div class="fw-bold text-dark small"><?php echo htmlspecialchars($user_full_name); ?></div>
                            <div class="text-muted" style="font-size: 0.75rem;"><?php echo ucfirst($role); ?></div>
                        </li>
                        <li>
                            <a class="dropdown-item py-2 text-danger" href="../logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div> 
        </div>
    </nav>
    <!-- Mobile Sidebar Toggle -->
    <button class="btn btn-primary d-md-none position-fixed" style="bottom: 20px; right: 20px; z-index: 1060; border-radius: 50%; width: 50px; height: 50px;" onclick="toggleSidebar()">
        <i class="bi bi-list"></i>
    </button>

    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-label">Main Menu</div>
        
        <a class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        
        <div class="sidebar-divider"></div>
        <div class="sidebar-label">Financial Management</div>
        
        <a class="nav-link <?php echo $current_page == 'accounts.php' ? 'active' : ''; ?>" href="accounts.php">
            <i class="bi bi-book"></i> Chart of Accounts
        </a>
        <a class="nav-link <?php echo $current_page == 'transactions.php' ? 'active' : ''; ?>" href="transactions.php">
            <i class="bi bi-arrow-left-right"></i> Journal Entries
        </a>
        <a class="nav-link <?php echo $current_page == 'budgets.php' ? 'active' : ''; ?>" href="budgets.php">
            <i class="bi bi-pie-chart"></i> Budget Management
        </a>
        
        <div class="sidebar-divider"></div>
        <div class="sidebar-label">Payments</div>
        
        <a class="nav-link <?php echo $current_page == 'student_payments.php' ? 'active' : ''; ?>" href="student_payments.php">
            <i class="bi bi-cash-coin"></i> Student Payments
        </a>
        <a class="nav-link <?php echo $current_page == 'employee_payments.php' ? 'active' : ''; ?>" href="employee_payments.php">
            <i class="bi bi-people"></i> Payroll
        </a>
        <a class="nav-link <?php echo $current_page == 'vendor_payments.php' ? 'active' : ''; ?>" href="vendor_payments.php">
            <i class="bi bi-truck"></i> Vendor Payments
        </a>
        
        <div class="sidebar-divider"></div>
        <div class="sidebar-label">Procurement</div>
        
        <a class="nav-link <?php echo $current_page == 'purchase_orders.php' ? 'active' : ''; ?>" href="purchase_orders.php">
            <i class="bi bi-cart"></i> Purchase Orders
        </a>
        
        <div class="sidebar-divider"></div>
        <div class="sidebar-label">Reports</div> 
        
        <a class="nav-link <?php echo $current_page == 'reports.php' ? 'active' : ''; ?>" href="reports.php">
            <i class="bi bi-file-earmark-bar-graph"></i> Financial Reports
        </a>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Toast Container -->
        <div class="toast-container position-fixed bottom-0 end-0 p-3">
            <div id="notificationToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <i class="bi bi-bell-fill text-primary me-2"></i>
                    <strong class="me-auto" id="toastTitle">Notification</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body" id="toastMessage"></div>
            </div>
        </div>
