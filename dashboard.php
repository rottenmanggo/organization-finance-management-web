<?php
session_start();
require_once "config/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

$currentPage = basename($_SERVER['PHP_SELF']);

// ================= DATA =================

// Total Income
$incomeQuery = $conn->query("SELECT SUM(amount) as total_income FROM income");
$totalIncome = $incomeQuery->fetch_assoc()['total_income'] ?? 0;

// Total Expense
$expenseQuery = $conn->query("SELECT SUM(amount) as total_expense FROM expense");
$totalExpense = $expenseQuery->fetch_assoc()['total_expense'] ?? 0;

// Saldo
$saldo = $totalIncome - $totalExpense;

// Total Members Aktif
$memberQuery = $conn->query("SELECT COUNT(*) as total_member FROM members WHERE status='aktif'");
$totalMembers = $memberQuery->fetch_assoc()['total_member'] ?? 0;

// Bulan & Tahun sekarang
$currentMonth = date('n');
$currentYear = date('Y');

// Belum bayar bulan ini
$kasQuery = $conn->query("
    SELECT COUNT(*) as belum_bayar 
    FROM kas 
    WHERE month = $currentMonth 
    AND year = $currentYear 
    AND status = 'belum'
");

$belumBayar = $kasQuery->fetch_assoc()['belum_bayar'] ?? 0;
?>

<!DOCTYPE html>
<html>

<head>
    <title>Dashboard | SIMAKAS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background-color: #f4f6f9;
        }

        .sidebar {
            height: 100vh;
            background: #1e293b;
            color: white;
            position: fixed;
            width: 240px;
        }

        .sidebar a {
            color: #cbd5e1;
            text-decoration: none;
            display: block;
            padding: 12px 20px;
            transition: 0.2s;
        }

        .sidebar a:hover {
            background: #334155;
            color: white;
        }

        .active-menu {
            background: #3b82f6 !important;
            color: white !important;
            font-weight: 500;
            border-left: 4px solid #60a5fa;
        }

        .content {
            margin-left: 240px;
            padding: 25px;
        }

        .card-stat {
            border-radius: 12px;
        }

        .icon-box {
            font-size: 28px;
            opacity: 0.8;
        }
    </style>
</head>

<body>

    <!-- SIDEBAR -->
    <div class="sidebar">
        <h4 class="text-center py-3 border-bottom">SIMAKAS</h4>

        <a href="dashboard.php" class="<?= ($currentPage == 'dashboard.php') ? 'active-menu' : '' ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <a href="members.php" class="<?= ($currentPage == 'members.php') ? 'active-menu' : '' ?>">
            <i class="bi bi-people"></i> Members
        </a>

        <a href="kas.php" class="<?= ($currentPage == 'kas.php') ? 'active-menu' : '' ?>">
            <i class="bi bi-cash"></i> Kas
        </a>

        <a href="income.php" class="<?= ($currentPage == 'income.php') ? 'active-menu' : '' ?>">
            <i class="bi bi-arrow-down-circle"></i> Income
        </a>

        <a href="expense.php" class="<?= ($currentPage == 'expense.php') ? 'active-menu' : '' ?>">
            <i class="bi bi-arrow-up-circle"></i> Expense
        </a>

        <a href="auth/logout.php" class="text-danger">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </div>

    <!-- CONTENT -->
    <div class="content">

        <!-- TOPBAR -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>Dashboard</h3>
            <div>
                <span class="me-2"><?= $_SESSION['username']; ?></span>
                <span class="badge bg-dark"><?= $_SESSION['role']; ?></span>
            </div>
        </div>

        <!-- STAT CARDS -->
        <div class="row">

            <div class="col-md-3 mb-4">
                <div class="card card-stat shadow-sm border-0">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Total Income</p>
                            <h5>Rp <?= number_format($totalIncome, 0, ',', '.'); ?></h5>
                        </div>
                        <div class="text-success icon-box">
                            <i class="bi bi-arrow-down-circle-fill"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card card-stat shadow-sm border-0">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Total Expense</p>
                            <h5>Rp <?= number_format($totalExpense, 0, ',', '.'); ?></h5>
                        </div>
                        <div class="text-danger icon-box">
                            <i class="bi bi-arrow-up-circle-fill"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card card-stat shadow-sm border-0">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Saldo</p>
                            <h5>Rp <?= number_format($saldo, 0, ',', '.'); ?></h5>
                        </div>
                        <div class="text-primary icon-box">
                            <i class="bi bi-wallet2"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card card-stat shadow-sm border-0">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Belum Bayar</p>
                            <h5><?= $belumBayar; ?> Orang</h5>
                        </div>
                        <div class="text-warning icon-box">
                            <i class="bi bi-exclamation-circle-fill"></i>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- MEMBER INFO -->
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h5>Total Anggota Aktif</h5>
                <h4><?= $totalMembers; ?> Orang</h4>
            </div>
        </div>

    </div>

</body>

</html>