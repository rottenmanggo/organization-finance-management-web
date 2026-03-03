<?php
session_start();
require_once "config/database.php";

$currentPage = basename($_SERVER['PHP_SELF']);

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

/* ================= DATA SUMMARY ================= */

$incomeQuery = $conn->query("SELECT SUM(amount) as total_income FROM income");
$totalIncome = $incomeQuery->fetch_assoc()['total_income'] ?? 0;

$expenseQuery = $conn->query("SELECT SUM(amount) as total_expense FROM expense");
$totalExpense = $expenseQuery->fetch_assoc()['total_expense'] ?? 0;

$saldo = $totalIncome - $totalExpense;

$memberQuery = $conn->query("SELECT COUNT(*) as total_member FROM members WHERE status='aktif'");
$totalMembers = $memberQuery->fetch_assoc()['total_member'] ?? 0;

/* ================= REKAP KAS BULAN INI ================= */

$currentMonth = date('n');
$currentYear = date('Y');
$kasPerMember = 10000;

/* Total member aktif */
$totalMemberAktif = $conn->query("
    SELECT COUNT(*) as total 
    FROM members 
    WHERE status='aktif'
")->fetch_assoc()['total'] ?? 0;

/* Total kas lunas bulan ini */
$kasMasuk = $conn->query("
    SELECT SUM(amount) as total 
    FROM kas 
    WHERE month=$currentMonth 
    AND year=$currentYear 
    AND status='lunas'
")->fetch_assoc()['total'] ?? 0;

/* Hitung member sudah bayar */
$memberLunas = $conn->query("
    SELECT COUNT(*) as total 
    FROM kas 
    WHERE month=$currentMonth 
    AND year=$currentYear 
    AND status='lunas'
")->fetch_assoc()['total'] ?? 0;

$memberBelum = $totalMemberAktif - $memberLunas;

$targetKas = $totalMemberAktif * $kasPerMember;

$progress = $targetKas > 0 ? ($kasMasuk / $targetKas) * 100 : 0;

/* Warna dinamis */
$progressColor = "#dc3545"; // merah
if ($progress >= 50)
    $progressColor = "#ffc107"; // kuning
if ($progress >= 80)
    $progressColor = "#28a745"; // hijau

/* ================= DATA GRAFIK PER BULAN ================= */

$year = date('Y');

$incomeData = [];
$expenseData = [];

for ($m = 1; $m <= 12; $m++) {

    $incomeMonth = $conn->query("
        SELECT SUM(amount) as total 
        FROM income 
        WHERE MONTH(date) = $m AND YEAR(date) = $year
    ");
    $incomeData[] = $incomeMonth->fetch_assoc()['total'] ?? 0;

    $expenseMonth = $conn->query("
        SELECT SUM(amount) as total 
        FROM expense 
        WHERE MONTH(date) = $m AND YEAR(date) = $year
    ");
    $expenseData[] = $expenseMonth->fetch_assoc()['total'] ?? 0;
}

/* ================= RECENT TRANSACTIONS ================= */

$recentQuery = $conn->query("
    SELECT date, description, amount, 'Income' as type FROM income
    UNION ALL
    SELECT date, description, amount, 'Expense' as type FROM expense
    ORDER BY date DESC
    LIMIT 5
");
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMAKAS Dashboard</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="assets/css/style.css">

</head>

<body>
    <div class="sidebar">
        <div>
            <h2>SIMAKAS</h2>
            <ul>
                <li>
                    <a href="dashboard.php" class="menu-link <?= $currentPage == 'dashboard.php' ? 'active' : '' ?>">
                        <img src="assets/dashboard.svg" class="menu-icon">
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="members.php" class="menu-link <?= $currentPage == 'members.php' ? 'active' : '' ?>">
                        <img src="assets/member.svg" class="menu-icon">
                        <span>Members</span>
                    </a>
                </li>
                <li>
                    <a href="kas.php" class="menu-link <?= $currentPage == 'kas.php' ? 'active' : '' ?>">
                        <img src="assets/kas.svg" class="menu-icon">
                        <span>Kas</span>
                    </a>
                <li>
                    <a href="transactions.php"
                        class="menu-link <?= $currentPage == 'transactions.php' ? 'active' : '' ?>">
                        <img src="assets/transaction.svg" class="menu-icon">
                        <span>Transactions</span>
                    </a>
                <li>
            </ul>
        </div>
        <div>
            <ul>
                <li><a href="auth/logout.php" onclick="return confirm('Yakin ingin logout?')"
                        class="btn btn-danger text-center d-inline-flex justify-content-center align-items-center">
                        Logout
                    </a></li>
            </ul>
        </div>
    </div>

    <div class="main">

        <div class="topbar">
            <h1>Dashboard</h1>
        </div>

        <!-- SUMMARY CARDS -->
        <div class="cards">
            <div class="card">
                <h3>Saldo</h3>
                <h2 style="color:#007bff">Rp <?= number_format($saldo, 0, ',', '.') ?></h2>
            </div>

            <div class="card">
                <h3>Total Income</h3>
                <h2 style="color:#28a745">Rp <?= number_format($totalIncome, 0, ',', '.') ?></h2>
            </div>

            <div class="card">
                <h3>Total Expense</h3>
                <h2 style="color:#dc3545">Rp <?= number_format($totalExpense, 0, ',', '.') ?></h2>
            </div>

            <div class="card">
                <h3>Total Members</h3>
                <h2><?= $totalMembers ?></h2>
            </div>
        </div>

        <!-- CARD KAS BULAN INI -->
        <div class="card kas-card">
            <div class="kas-header">
                <h3>Kas Bulan <?= date("F Y") ?></h3>
                <span class="kas-progress"><?= round($progress) ?>%</span>
            </div>

            <div class="kas-grid">
                <div>
                    <small>Target</small>
                    <h4>Rp <?= number_format($targetKas, 0, ',', '.') ?></h4>
                </div>

                <div>
                    <small>Realisasi</small>
                    <h4>Rp <?= number_format($kasMasuk, 0, ',', '.') ?></h4>
                </div>

                <div>
                    <small>Belum Bayar</small>
                    <h4 style="color:#dc3545"><?= $memberBelum ?> Member</h4>
                </div>
            </div>

            <div class="progress-container">
                <div class="progress-fill"
                    style="width: <?= min($progress, 100) ?>%; background: <?= $progressColor ?>;">
                </div>
            </div>
        </div>

        <!-- CHART -->
        <div class="card">
            <h3>Financial Overview (2026)</h3>
            <div class="chart-container">
                <canvas id="myChart"></canvas>
            </div>
        </div>

        <!-- TABLE -->
        <div class="table-container">
            <h3>Laporan Transaksi</h3>
            <table>
                <tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Type</th>
                    <th>Amount</th>
                </tr>

                <?php while ($row = $recentQuery->fetch_assoc()): ?>
                    <tr>
                        <td><?= date("d-m-Y", strtotime($row['date'])) ?></td>
                        <td><?= htmlspecialchars($row['description']) ?></td>
                        <td style="color:<?= $row['type'] == 'Income' ? '#28a745' : '#dc3545' ?>;font-weight:600">
                            <?= $row['type'] ?>
                        </td>
                        <td>Rp <?= number_format($row['amount'], 0, ',', '.') ?></td>
                    </tr>
                <?php endwhile; ?>

            </table>
        </div>

    </div>

    <script>
        const ctx = document.getElementById('myChart');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Income',
                    data: <?= json_encode($incomeData ?? []) ?>,
                    backgroundColor: '#28a745'
                }, {
                    label: 'Expense',
                    data: <?= json_encode($expenseData ?? []) ?>,
                    backgroundColor: '#dc3545'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    </script>

</body>

</html>