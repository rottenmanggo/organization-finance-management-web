<?php
session_start();
require_once "config/database.php";

$currentPage = basename($_SERVER['PHP_SELF']);

$currentPage = basename($_SERVER['PHP_SELF']);

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

/* ================= FILTER ================= */
$selectedMonth = $_GET['month'] ?? 'all';
$selectedYear = $_GET['year'] ?? date('Y');

if ($selectedMonth === 'all') {
    $where = ""; // Tidak pakai filter
} else {
    $selectedMonth = (int) $selectedMonth;
    $selectedYear = (int) $selectedYear;
    $where = "WHERE MONTH(date) = $selectedMonth AND YEAR(date) = $selectedYear";
}
/* ================= TAMBAH ================= */
if (isset($_POST['tambah'])) {

    $type = $_POST['type'];
    $desc = $_POST['description'];
    $amount = $_POST['amount'];
    $date = $_POST['date'];

    if ($type == "Income") {
        $stmt = $conn->prepare("INSERT INTO income (description, amount, date) VALUES (?, ?, ?)");
    } else {
        $stmt = $conn->prepare("INSERT INTO expense (description, amount, date) VALUES (?, ?, ?)");
    }

    $stmt->bind_param("sds", $desc, $amount, $date);
    $stmt->execute();

    header("Location: transactions.php?month=$selectedMonth&year=$selectedYear");
    exit;
}

/* ================= HAPUS ================= */
if (isset($_GET['hapus']) && isset($_GET['type'])) {

    $id = (int) $_GET['hapus'];
    $type = $_GET['type'];

    if ($type == "Income") {
        $conn->query("DELETE FROM income WHERE id=$id");
    } else {
        $conn->query("DELETE FROM expense WHERE id=$id");
    }

    header("Location: transactions.php?month=$selectedMonth&year=$selectedYear");
    exit;
}

/* ================= DATA ================= */
$data = $conn->query("
    SELECT id, date, description, amount, 'Income' as type FROM income $where
    UNION ALL
    SELECT id, date, description, amount, 'Expense' as type FROM expense $where
    ORDER BY date DESC
");

$totalIncome = $conn->query("SELECT SUM(amount) as total FROM income $where")->fetch_assoc()['total'] ?? 0;
$totalExpense = $conn->query("SELECT SUM(amount) as total FROM expense $where")->fetch_assoc()['total'] ?? 0;
$saldo = $totalIncome - $totalExpense;
?>
<!doctype html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Transactions | SIMAKAS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

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

        /* ICON */
        .menu-link {
            display: flex !important;
            align-items: center;
            gap: 10px;
        }

        .menu-icon {
            width: 18px;
            height: 18px;
            object-fit: contain;
            filter: invert(1);
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
            border-radius: 8px;
            transition: .3s;
            font-size: 14px;
        }

        /* Hover hanya untuk menu biasa, bukan tombol */
        .sidebar ul li a:not(.btn):hover,
        .sidebar ul li a:not(.btn).active {
            background: #007bff;
            color: #fff;
        }

        .sidebar ul li a.btn-danger {
            transition: all 0.2s ease;
        }

        .sidebar ul li a.btn-danger:hover {
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(220, 53, 69, 0.35);
        }

        .sidebar ul li a.btn-danger:active {
            transform: translateY(1px);
            box-shadow: 0 3px 8px rgba(220, 53, 69, 0.25);
        }

        /* Khusus logout jangan ikut hover biru */
        .sidebar ul li a.btn-danger:hover,
        .sidebar ul li a.btn-danger:active {
            background: #dc3545;
            color: #fff;
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px
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
        }

        .card h3 {
            font-size: 14px;
            color: #888
        }

        .card h2 {
            margin-top: 10px
        }

        /* BUTTON */

        .btn {
            padding: 8px 14px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            min-width: 90px;
            text-align: center;
            text-decoration: none;
        }

        .btn-primary {
            background: #007bff;
            color: #fff;
        }

        .btn-danger {
            background: #dc3545;
            color: #fff;
        }

        /* TABLE */
        .table-container {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, .05);
        }

        table {
            width: 100%;
            border-collapse: collapse
        }

        th,
        td {
            padding: 12px;
            font-size: 14px;
            text-align: left
        }

        th {
            background: #f1f1f1
        }

        tr:not(:last-child) {
            border-bottom: 1px solid #eee
        }

        /* FILTER */
        .form-input {
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-size: 14px;
        }

        /* MODAL */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, .4);
            display: none;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            width: 350px;
        }

        .modal input,
        .modal select {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }

        /* Export button hover */
        .export-btn {
            transition: 0.2s ease;
            margin-bottom: 10px;
        }

        .export-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(216, 216, 216, 0.3);
        }

        /* Modal style */
        .export-modal {
            text-align: center;
            padding: 30px;
            width: 400px;
        }

        .export-modal h3 {
            margin-bottom: 10px;
        }

        .export-modal p {
            color: #666;
            margin-bottom: 20px;
        }

        .modal-actions {
            display: flex;
            justify-content: center;
            gap: 15px;
        }
    </style>
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
            <h1>Transactions</h1>
            <button class="btn btn-primary" onclick="openModal()">+ Tambah Transaksi</button>
        </div>

        <!-- SUMMARY -->
        <div class="cards">
            <div class="card">
                <h3>Total Income</h3>
                <h2 style="color:#28a745">Rp <?= number_format($totalIncome, 0, ',', '.') ?></h2>
            </div>
            <div class="card">
                <h3>Total Expense</h3>
                <h2 style="color:#dc3545">Rp <?= number_format($totalExpense, 0, ',', '.') ?></h2>
            </div>
            <div class="card">
                <h3>Saldo</h3>
                <h2 style="color:#007bff">Rp <?= number_format($saldo, 0, ',', '.') ?></h2>
            </div>
        </div>

        <!-- FILTER -->
        <div class="card" style="margin-bottom:25px;">
            <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
                <select name="month" class="form-input">
                    <option value="all" <?= ($selectedMonth == 'all') ? 'selected' : '' ?>>
                        Semua
                    </option>

                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= $m ?>" <?= ($selectedMonth == $m) ? 'selected' : '' ?>>
                            <?= date("F", mktime(0, 0, 0, $m, 1)) ?>
                        </option>
                    <?php endfor; ?>
                </select>
                <input type="number" name="year" value="<?= $selectedYear ?>" class="form-input" style="width:100px">
                <button class="btn btn-primary">Filter</button>
            </form>
        </div>

        <button class="btn btn-success export-btn" onclick="confirmExport()">
            Export CSV
        </button>

        <!-- TABLE -->
        <div class="table-container">
            <table>
                <tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th></th>
                </tr>

                <?php while ($row = $data->fetch_assoc()): ?>
                    <tr>
                        <td><?= date("d-m-Y", strtotime($row['date'])) ?></td>

                        <td><?= htmlspecialchars($row['description']) ?></td>

                        <td style="color:<?= $row['type'] == 'Income' ? '#28a745' : '#dc3545' ?>;font-weight:600">
                            <?= $row['type'] ?>
                        </td>

                        <td>Rp <?= number_format($row['amount'], 0, ',', '.') ?></td>

                        <td style="text-align:right;">
                            <a href="?hapus=<?= $row['id'] ?>&type=<?= $row['type'] ?>" class="btn btn-danger"
                                onclick="return confirm('Yakin hapus?')">
                                Hapus
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>


    </div>

    <!-- MODAL TAMBAH -->
    <div class="modal" id="modal">
        <div class="modal-content">
            <h3>Tambah Transaksi</h3>
            <form method="POST">
                <select name="type" required>
                    <option value="">Pilih Type</option>
                    <option value="Income">Income</option>
                    <option value="Expense">Expense</option>
                </select>

                <input type="text" name="description" placeholder="Description" required>
                <input type="number" name="amount" placeholder="Amount" required>
                <input type="date" name="date" required>

                <button type="submit" name="tambah" class="btn btn-primary">Simpan</button>
                <button type="button" onclick="closeModal()" class="btn btn-danger">Batal</button>
            </form>
        </div>
    </div>

    <script>
        function openModal() { document.getElementById("modal").style.display = "flex"; }
        function closeModal() { document.getElementById("modal").style.display = "none"; }
    </script>

    <script>
        function confirmExport() {
            document.getElementById("exportModal").style.display = "flex";
        }

        function closeExport() {
            document.getElementById("exportModal").style.display = "none";
        }

        function doExport() {
            window.location.href = "export_transactions.php";
        }
    </script>
    <div class="modal" id="exportModal">
        <div class="modal-content export-modal">
            <h3>Export Laporan</h3>
            <p>Apakah Anda yakin ingin mengunduh laporan transaksi?</p>

            <div class="modal-actions">
                <button class="btn btn-success" onclick="doExport()">Ya</button>
                <button class="btn btn-danger" onclick="closeExport()">Batal</button>
            </div>
        </div>
    </div>

</body>

</html>