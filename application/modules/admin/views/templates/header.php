<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' - ' : '' ?>Sistem Tracer Study</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --sidebar-width: 250px;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            overflow-y: auto;
            z-index: 1000;
            transition: all 0.3s;
        }

        .sidebar-brand {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-brand h4 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
        }

        .sidebar-menu {
            list-style: none;
            padding: 20px 0;
            margin: 0;
        }

        .sidebar-menu li {
            margin: 5px 0;
        }

        .sidebar-menu a {
            display: block;
            padding: 12px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left: 3px solid white;
        }

        .sidebar-menu a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
            transition: all 0.3s;
        }

        /* Top Navbar */
        .top-navbar {
            background: white;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin: -20px -20px 20px -20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        /* Page Header */
        .page-header {
            margin-bottom: 30px;
        }

        .page-header h1 {
            font-size: 24px;
            font-weight: 600;
            color: #333;
            margin: 0;
        }

        .page-header p {
            color: #666;
            margin: 5px 0 0;
        }

        /* Card Styles */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }

        .card-header {
            background: white;
            border-bottom: 1px solid #eee;
            padding: 15px 20px;
            font-weight: 600;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <h4><i class="bi bi-graph-up"></i> Tracer Study</h4>
        </div>
        <ul class="sidebar-menu">
            <li><a href="<?= base_url('admin/dashboard') ?>" class="<?= ($this->uri->segment(2) == 'dashboard') ? 'active' : '' ?>"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            
            <?php if ($this->session->userdata('role') === 'super_admin'): ?>
                <!-- Menu khusus Super Admin -->
                <li><a href="<?= base_url('admin/users') ?>"><i class="bi bi-people"></i> Manajemen User</a></li>
                <li><a href="<?= base_url('admin/audit') ?>"><i class="bi bi-shield-check"></i> Audit Trail</a></li>
                <li><a href="<?= base_url('admin/settings') ?>"><i class="bi bi-gear"></i> Pengaturan</a></li>
            <?php endif; ?>
            
            <?php if ($this->session->userdata('role') === 'super_admin' || $this->session->userdata('role') === 'admin_pusat_karir'): ?>
                <!-- Menu bersama Super Admin dan Admin Pusat Karir -->
                <li><a href="<?= base_url('admin/alumni') ?>"><i class="bi bi-person-badge"></i> Data Alumni</a></li>
                <li><a href="<?= base_url('admin/surveys') ?>"><i class="bi bi-card-checklist"></i> Survei</a></li>
                <li><a href="<?= base_url('admin/reports') ?>"><i class="bi bi-file-earmark-bar-graph"></i> Laporan</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <div class="top-navbar">
            <div>
                <button class="btn btn-outline-secondary d-md-none" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
            </div>
            <div class="user-menu">
                <span><?= isset($user_data->username) ? htmlspecialchars($user_data->username) : 'User' ?></span>
                <div class="user-avatar"><?= strtoupper(substr(isset($user_data->username) ? $user_data->username : 'U', 0, 1)) ?></div>
                <a href="<?= base_url('logout') ?>" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>

        <!-- Page Header -->
        <div class="page-header">
            <h1><?= isset($page_title) ? $page_title : 'Dashboard' ?></h1>
            <?php if (isset($page_subtitle)): ?>
            <p><?= $page_subtitle ?></p>
            <?php endif; ?>
        </div>
