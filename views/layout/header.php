<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?? 'Tabulation System' ?></title>
    <meta name="csrf-token" content="<?= Session::getCSRFToken() ?>">

    <!-- Google Font: Inter -->
    
    
    <link rel="stylesheet" href="/tabulation/public/assets/css/inter-font.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="/tabulation/public/assets/css/font-awesome.min.css">
    
    <!-- AdminLTE (for components only) -->
    <link rel="stylesheet" href="/tabulation/public/assets/css/adminlte.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="/tabulation/public/assets/css/sweetalert2.min.css">
    
    <style>
        :root {
            --primary: #373737ff;
            --secondary: #00d9ff;
            --accent: #ffffff;
            --bg: #fafafa;
            --bg-dark: #1a1a1a;
            --text: #0a0a0a;
            --text-light: #666;
            --border: #e5e5e5;
            --hover: #f5f5f5;
            --sidebar-bg: #302e2eff;
            --sidebar-text: rgba(255,255,255,0.8);
            --sidebar-active: #00d9ff;
        }
        
        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
        .header-logo {
            width:200px;
            height: auto;
        }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--bg);
            color: var(--text);
            font-weight: 400;
        }
        
        /* Sidebar */
        .main-sidebar {
            background: var(--sidebar-bg) !important;
            border-right: none;
        }
        
        .main-sidebar .brand-link {
            border-bottom: 1px solid rgba(255,255,255,0.1);
            padding: 1.5rem 1.25rem;
            background: var(--primary);
        }
        
        .main-sidebar .brand-text {
            font-weight: 700;
            color: var(--accent);
            font-size: 1.25rem;
            letter-spacing: -0.02em;
        }
        
        .main-sidebar .nav-sidebar .nav-item .nav-link {
            color: var(--sidebar-text);
            padding: 0.875rem 1.25rem;
            border-radius: 0;
            margin: 0;
            transition: all 0.2s;
            border-left: 3px solid transparent;
            font-weight: 500;
        }
        
        .main-sidebar .nav-sidebar .nav-item .nav-link:hover {
            background: rgba(0,217,255,0.1);
            color: var(--accent);
            border-left-color: var(--secondary);
        }
        
        .main-sidebar .nav-sidebar .nav-item .nav-link.active {
            background: rgba(0,217,255,0.15);
            color: var(--sidebar-active);
            border-left-color: var(--secondary);
            font-weight: 600;
        }
        
        .main-sidebar .nav-sidebar .nav-item .nav-link i {
            width: 20px;
            margin-right: 12px;
        }
        
        .main-sidebar .user-panel {
            border-top: 1px solid rgba(255,255,255,0.1);
            padding-top: 1rem;
            margin-top: 1rem;
        }
        
        .main-sidebar .user-panel .info a {
            color: var(--accent);
            font-weight: 600;
        }
        
        .main-sidebar .user-panel .info small {
            color: var(--sidebar-text);
            font-size: 0.8125rem;
        }
        
        /* Header */
        .main-header {
            background: var(--accent) !important;
            border-bottom: 1px solid var(--border);
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        
        .main-header .navbar-nav .nav-link {
            color: var(--text);
            font-weight: 500;
        }
        
        /* Content */
        .content-wrapper {
            background: var(--bg);
        }
        
        .content-header h1 {
            font-weight: 700;
            color: var(--primary);
            letter-spacing: -0.03em;
            font-size: 1.75rem;
        }
        
        .breadcrumb {
            background: transparent;
            padding: 0;
        }
        
        .breadcrumb-item a {
            color: var(--text-light);
            font-weight: 500;
        }
        
        .breadcrumb-item.active {
            color: var(--text);
        }
        
        /* Cards */
        .card {
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            background: var(--accent);
        }
        
        .card-header {
            background: var(--accent);
            border-bottom: 1px solid var(--border);
            padding: 1.5rem;
            font-weight: 600;
            font-size: 1.125rem;
        }
        
        .card-primary.card-outline {
            border-top: 3px solid var(--secondary);
        }
        
        /* Buttons */
        .btn-primary {
            background: var(--primary);
            border-color: var(--primary);
            border-radius: 8px;
            font-weight: 600;
            padding: 0.625rem 1.5rem;
            color: var(--accent);
            transition: all 0.2s;
        }
        
        .btn-primary:hover {
            background: #1a1a1a;
            border-color: #1a1a1a;
            color: var(--accent);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(10,10,10,0.15);
        }
        
        .btn-secondary {
            background: var(--accent);
            border-color: var(--border);
            color: var(--text);
            border-radius: 8px;
            font-weight: 500;
        }
        
        .btn-secondary:hover {
            background: var(--hover);
            border-color: var(--border);
        }
        
        .btn-sm {
            padding: 0.375rem 0.875rem;
            font-size: 0.875rem;
        }
        
        /* Tables */
        .table {
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .table thead th {
            border-bottom: 2px solid var(--border);
            font-weight: 600;
            color: var(--primary);
            text-transform: none;
            font-size: 0.875rem;
            letter-spacing: -0.01em;
            padding: 1rem;
            background: var(--bg);
        }
        
        .table tbody tr {
            transition: background 0.2s;
        }
        
        .table tbody tr:hover {
            background: var(--hover);
        }
        
        .table tbody td {
            padding: 1rem;
            border-bottom: 1px solid var(--border);
        }
        
        /* Forms */
        .form-control {
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 0.625rem 0.875rem;
            font-size: 0.9375rem;
            transition: all 0.2s;
        }
        
        .form-control:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(0,217,255,0.1);
            outline: none;
        }
        
        .form-control-lg {
            padding: 0.875rem 1rem;
            font-size: 1rem;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--text);
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }
        
        /* Alerts */
        .alert {
            border-radius: 8px;
            border: none;
            padding: 1rem 1.25rem;
        }
        
        .alert-success {
            background: #f0fdf4;
            color: #166534;
            border-left: 4px solid #22c55e;
        }
        
        .alert-danger {
            background: #fef2f2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }
        
        .alert-info {
            background: #eff6ff;
            color: #1e40af;
            border-left: 4px solid var(--secondary);
        }
        
        .alert-warning {
            background: #fffbeb;
            color: #92400e;
            border-left: 4px solid #f59e0b;
        }
        
        /* Badges */
        .badge {
            border-radius: 6px;
            font-weight: 600;
            padding: 0.4em 0.75em;
            font-size: 0.8125rem;
        }
        
        .badge-primary {
            background: var(--primary);
            color: var(--accent);
        }
        
        .badge-success {
            background: #22c55e;
            color: white;
        }
        
        .badge-warning {
            background: #f59e0b;
            color: white;
        }
        
        .badge-secondary {
            background: #6b7280;
            color: white;
        }
        
        .badge-info {
            background: var(--secondary);
            color: var(--primary);
        }
        
        .badge-sm {
            font-size: 0.7rem;
            padding: 0.2em 0.5em;
            margin-left: 5px;
        }
        
        /* Small Boxes (Stats) */
        .small-box {
            background: var(--accent);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
        }
        
        .small-box .inner {
            padding: 1.5rem;
        }
        
        .small-box .inner h3 {
            font-weight: 700;
            color: var(--primary);
            font-size: 2.25rem;
            margin-bottom: 0.5rem;
            letter-spacing: -0.02em;
        }
        
        .small-box .inner p {
            color: var(--text-light);
            font-size: 0.875rem;
            font-weight: 500;
            margin: 0;
        }
        
        .small-box .icon {
            color: var(--border);
            opacity: 0.3;
        }
        
        .small-box-footer {
            background: var(--bg);
            border-top: 1px solid var(--border);
            color: var(--text-light);
            font-weight: 500;
            padding: 0.75rem;
        }
        
        .small-box-footer:hover {
            background: var(--hover);
            color: var(--primary);
        }
        
        /* Tabs */
        .nav-tabs {
            border-bottom: 2px solid var(--border);
        }
        
        .nav-tabs .nav-link {
            border: none;
            border-bottom: 2px solid transparent;
            color: var(--text-light);
            font-weight: 500;
            padding: 0.875rem 1.25rem;
            margin-right: 0.5rem;
            border-radius: 0;
        }
        
        .nav-tabs .nav-link:hover {
            border-bottom-color: var(--border);
            color: var(--text);
        }
        
        .nav-tabs .nav-link.active {
            color: var(--primary);
            border-bottom-color: var(--secondary);
            font-weight: 600;
        }
        
        /* Button Groups */
        .btn-group-toggle .btn {
            border: 1px solid var(--border);
            color: var(--text);
            font-weight: 500;
        }
        
        .btn-group-toggle .btn.active {
            background: var(--primary);
            color: var(--accent);
            border-color: var(--primary);
        }
        
        /* Progress Bars */
        .progress {
            background: var(--border);
            border-radius: 8px;
            height: 8px;
        }
        
        .progress-bar {
            background: var(--secondary);
            border-radius: 8px;
        }
        
        .progress-sm {
            height: 6px;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini sidebar-collapse layout-fixed">
    <?php if (Session::has('user_id')): ?>
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                        <i class="fas fa-bars"></i>
                    </a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <i class="far fa-user"></i>
                        <span class="ml-1"><?= htmlspecialchars(Session::get('full_name') ?? 'User') ?></span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <span class="dropdown-item dropdown-header">
                            <i class="fas fa-user-circle mr-2"></i>
                            <?= htmlspecialchars(Session::get('role_name') ?? 'Guest') ?>
                        </span>
                        <div class="dropdown-divider"></div>
                        <a href="/tabulation/logout" class="dropdown-item">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </a>
                    </div>
                </li>
            </ul>
        </nav>

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="/tabulation/dashboard" class="brand-link">
                <span class="brand-text font-weight-light">Tabulation</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar user panel -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <i class="fas fa-user-circle fa-2x" style="color: var(--sidebar-text);"></i>
                    </div>
                    <div class="info">
                        <a href="#" class="d-block">
                            <?= htmlspecialchars(Session::get('full_name') ?? 'User') ?>
                        </a>
                        <small>
                            <?= htmlspecialchars(Session::get('role_name') ?? 'Guest') ?>
                        </small>
                    </div>
                </div>

                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                        <li class="nav-item">
                            <a href="/tabulation/dashboard" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/dashboard') !== false) ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        <?php if (Session::get('role_name') !== 'Judge'): ?>
                        <li class="nav-item">
                            <a href="/tabulation/events" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/events') !== false && strpos($_SERVER['REQUEST_URI'] ?? '', '/display') === false) ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-calendar-alt"></i>
                                <p>Events</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (Session::get('role_name') === 'Super Admin' || Session::get('role_name') === 'Event Admin' || Session::get('role_name') === 'Event Organizer'): ?>
                        <li class="nav-item">
                            <a href="/tabulation/user-management" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/user-management') !== false) ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-users-cog"></i>
                                <p>Users</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/tabulation/criteria-management" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/criteria-management') !== false) ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-list-check"></i>
                                <p>Criteria</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/tabulation/judge-management" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/judge-management') !== false) ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-users"></i>
                                <p>Judges</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/tabulation/judge-connections" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/judge-connections') !== false) ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-users-cog"></i>
                                <p>Judge Connections</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (Session::get('role_name') === 'Judge'): ?>
                        <li class="nav-item">
                            <a href="/tabulation/judge/rounds" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/judge/rounds') !== false) ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-clipboard-check"></i>
                                <p>My Rounds</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (Session::get('role_name') !== 'Judge'): ?>
                        <?php
                        // Get unread notification count
                        $unreadCount = 0;
                        if (Session::has('user_id')) {
                            require_once __DIR__ . '/../../core/Database.php';
                            $db = Database::getInstance();
                            $unreadCount = $db->fetchOne(
                                "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0",
                                [Session::get('user_id')]
                            )['count'] ?? 0;
                        }
                        ?>
                        <li class="nav-item">
                            <a href="/tabulation/notifications" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/notifications') !== false) ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-bell"></i>
                                <p>
                                    Notifications
                                    <?php if ($unreadCount > 0): ?>
                                        <span class="badge badge-danger badge-sm"><?= $unreadCount ?></span>
                                    <?php endif; ?>
                                </p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <!-- <li class="nav-item">
                            <a href="/tabulation/display/lineup" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/display/lineup') !== false) ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-tv"></i>
                                <p>Live Lineup</p>
                            </a>
                        </li> -->
                        <li class="nav-item">
                            <a href="/tabulation/logout" class="nav-link">
                                <i class="nav-icon fas fa-sign-out-alt"></i>
                                <p>Logout</p>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <!-- Content Header -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0"><?= $title ?? 'Dashboard' ?></h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="/tabulation/dashboard">Home</a></li>
                                <li class="breadcrumb-item active"><?= $title ?? 'Dashboard' ?></li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
    <?php endif; ?>
    
    <?php if (Session::has('success_message')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="icon fas fa-check"></i> <?= htmlspecialchars(Session::get('success_message') ?? '') ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php Session::remove('success_message'); ?>
    <?php endif; ?>
    
    <?php if (Session::has('error_message')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="icon fas fa-ban"></i> <?= htmlspecialchars(Session::get('error_message') ?? '') ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php Session::remove('error_message'); ?>
    <?php endif; ?>
