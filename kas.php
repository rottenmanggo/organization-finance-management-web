<?php
session_start();
require_once "config/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

$currentPage = basename($_SERVER['PHP_SELF']);

$selectedMonth = $_GET['month'] ?? date('n');
$selectedYear = $_GET['year'] ?? date('Y');

/* ================= SIMPAN PEMBAYARAN ================= */
if (isset($_POST['bayar'])) {

    $member_id = $_POST['member_id'];
    $amount = $_POST['amount'];
    $month = $_POST['month'];
    $year = $_POST['year'];

    $check = $conn->prepare("SELECT id FROM kas WHERE member_id=? AND month=? AND year=?");
    $check->bind_param("iii", $member_id, $month, $year);
    $check->execute();
    $resultCheck = $check->get_result();

    if ($resultCheck->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE kas SET amount=?, status='lunas' WHERE member_id=? AND month=? AND year=?");
        $stmt->bind_param("diii", $amount, $member_id, $month, $year);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO kas (member_id, month, year, amount, status) VALUES (?, ?, ?, ?, 'lunas')");
        $stmt->bind_param("iiid", $member_id, $month, $year, $amount);
        $stmt->execute();
    }
}

/* ================= DATA MEMBER ================= */
$members = $conn->query("
    SELECT m.*, k.amount, k.status 
    FROM members m
    LEFT JOIN kas k 
        ON m.id = k.member_id 
        AND k.month = $selectedMonth 
        AND k.year = $selectedYear
    WHERE m.status='aktif'
");
?>
<!DOCTYPE html>
<html>

<head>
    <title>Kas | SIMAKAS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: #f4f6f9;
        }

        .sidebar {
            height: 100vh;
            background: #1e293b;
            position: fixed;
            width: 240px;
            color: white;
        }

        .sidebar a {
            display: block;
            padding: 12px 20px;
            color: #cbd5e1;
            text-decoration: none;
        }

        .sidebar a:hover {
            background: #334155;
            color: white;
        }

        .active-menu {
            background: #3b82f6 !important;
            color: white !important;
            border-left: 4px solid #60a5fa;
        }

        .content {
            margin-left: 240px;
            padding: 25px;
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

        <h3 class="mb-4">Kas Bulanan</h3>

        <!-- FILTER BULAN -->
        <form class="row mb-4" method="GET">
            <div class="col-md-3">
                <select name="month" class="form-select">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= $m ?>" <?= ($selectedMonth == $m) ? 'selected' : '' ?>>
                            <?= date("F", mktime(0, 0, 0, $m, 1)); ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-3">
                <input type="number" name="year" class="form-control" value="<?= $selectedYear ?>">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary">Filter</button>
            </div>
        </form>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Divisi</th>
                            <th>Status</th>
                            <th>Nominal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php $no = 1;
                        while ($row = $members->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?= $no++ ?>
                                </td>
                                <td>
                                    <?= $row['name'] ?>
                                </td>
                                <td>
                                    <?= $row['division'] ?>
                                </td>

                                <td>
                                    <?php if ($row['status'] == 'lunas'): ?>
                                        <span class="badge bg-success">Lunas</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Belum</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?= $row['amount'] ? "Rp " . number_format($row['amount'], 0, ',', '.') : "-" ?>
                                </td>

                                <td>
                                    <form method="POST" class="d-flex">
                                        <input type="hidden" name="member_id" value="<?= $row['id'] ?>">
                                        <input type="hidden" name="month" value="<?= $selectedMonth ?>">
                                        <input type="hidden" name="year" value="<?= $selectedYear ?>">
                                        <input type="number" name="amount" class="form-control me-2" placeholder="Nominal"
                                            required>
                                        <button name="bayar" class="btn btn-success btn-sm">
                                            Bayar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>

                    </tbody>
                </table>
            </div>
        </div>

    </div>
</body>

</html>