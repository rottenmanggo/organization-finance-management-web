<?php
session_start();
require_once "config/database.php";

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

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif
        }

        body {
            display: flex;
            background: #f4f6f9
        }

        /* SIDEBAR */
        .sidebar {
            width: 240px;
            height: 100vh;
            background: #1e1e2f;
            color: #fff;
            padding: 30px 20px;
            position: fixed;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .sidebar h2 {
            margin-bottom: 40px
        }

        .sidebar ul {
            list-style: none
        }

        .sidebar ul li {
            margin: 15px 0
        }

        .sidebar ul li a {
            color: #ccc;
            text-decoration: none;
            display: block;
            padding: 8px 10px;
            border-radius: 6px;
            transition: .3s;
            font-size: 14px;
        }

        .sidebar ul li a:hover,
        .sidebar ul li a.active {
            background: #007bff;
            color: #fff
        }

        .logout {
            color: #ff4d4d
        }

        /* MAIN */
        .main {
            margin-left: 240px;
            padding: 30px;
            width: 100%
        }

        .topbar {
            margin-bottom: 30px
        }

        .topbar h1 {
            font-size: 22px
        }

        /* CARDS */
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, .05);
            transition: .3s;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, .08);
        }

        .card h3 {
            font-size: 14px;
            color: #888
        }

        .card h2 {
            margin-top: 10px
        }

        /* CHART */
        .chart-container {
            height: 300px
        }

        /* TABLE */
        .table-container {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, .05);
            margin-top: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse
        }

        th,
        td {
            padding: 12px;
            font-size: 14px
        }

        th {
            background: #f1f1f1
        }

        tr:not(:last-child) {
            border-bottom: 1px solid #eee
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <div>
            <h2>SIMAKAS</h2>
            <ul>
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="members.php">Members</a></li>
                <li><a href="kas.php">Kas</a></li>
                <li><a href="transactions.php">Transactions</a></li>
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

        <!-- CHART -->
        <div class="card chart-container">
            <h3>Financial Overview (<?= $year ?>)</h3>
            <canvas id="financeChart"></canvas>
        </div>

        <!-- TABLE -->
        <div class="table-container">
            <h3>Recent Transactions</h3>
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
        const ctx = document.getElementById("financeChart");

        new Chart(ctx, {
            type: "bar",
            data: {
                labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                datasets: [
                    {
                        label: "Income",
                        data: <?= json_encode($incomeData); ?>,
                        backgroundColor: "#28a745",
                        borderRadius: 6
                    },
                    {
                        label: "Expense",
                        data: <?= json_encode($expenseData); ?>,
                        backgroundColor: "#dc3545",
                        borderRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: "top" }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>

</body>

</html>