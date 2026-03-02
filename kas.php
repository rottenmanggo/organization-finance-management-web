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

$selectedMonth = $_GET['month'] ?? date('n');
$selectedYear = $_GET['year'] ?? date('Y');

/* ================= BATAL ================= */
if (isset($_POST['batal'])) {

    $member_id = $_POST['member_id'];
    $month = $_POST['month'];
    $year = $_POST['year'];

    $stmt = $conn->prepare("UPDATE kas SET status='belum', amount=NULL WHERE member_id=? AND month=? AND year=?");
    $stmt->bind_param("iii", $member_id, $month, $year);
    $stmt->execute();

    header("Location: kas.php?month=$month&year=$year");
    exit;
}

/* ================= BAYAR ================= */
if (isset($_POST['bayar'])) {

    $member_id = $_POST['member_id'];
    $month = $_POST['month'];
    $year = $_POST['year'];
    $amount = 10000;

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

    header("Location: kas.php?month=$month&year=$year");
    exit;
}

/* ================= DATA ================= */
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

<!doctype html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Kas | SIMAKAS</title>
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
            margin-bottom: 25px
        }

        .topbar h1 {
            font-size: 22px
        }

        /* CARD */
        .card {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, .05);
        }

        /* FILTER */
        .filter-box {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, .05);
            margin-bottom: 20px;
        }

        .filter-box select,
        .filter-box input {
            padding: 8px;
            border-radius: 6px;
            border: 1px solid #ccc;
            margin-right: 10px;
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
        }

        .btn-primary {
            background: #007bff;
            color: #fff
        }

        .btn-success {
            background: #28a745;
            color: #fff
        }

        .btn-danger {
            background: #dc3545;
            color: #fff
        }

        /* TABLE */
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
            background: #f1f1f1;
            text-align: left
        }

        tr:not(:last-child) {
            border-bottom: 1px solid #eee
        }

        /* LOCK COLUMN WIDTH */
        th:nth-child(1),
        td:nth-child(1) {
            width: 60px;
            text-align: center;
        }

        th:nth-child(2),
        td:nth-child(2) {
            width: 220px;
        }

        th:nth-child(3),
        td:nth-child(3) {
            width: 180px;
        }

        th:nth-child(4),
        td:nth-child(4) {
            width: 120px;
        }

        th:nth-child(5),
        td:nth-child(5) {
            width: 260px;
        }

        /* BADGE */
        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
        }

        .badge-success {
            background: #d4edda;
            color: #155724
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <div>
            <h2>SIMAKAS</h2>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="members.php">Members</a></li>
                <li><a href="kas.php" class="active">Kas</a></li>
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
            <h1>Kas Bulanan</h1>
        </div>

        <div class="filter-box">
            <form method="GET">
                <select name="month">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= $m ?>" <?= ($selectedMonth == $m) ? 'selected' : '' ?>>
                            <?= date("F", mktime(0, 0, 0, $m, 1)) ?>
                        </option>
                    <?php endfor; ?>
                </select>

                <input type="number" name="year" value="<?= $selectedYear ?>" style="width:100px">
                <button class="btn btn-primary">Filter</button>
            </form>
        </div>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Divisi</th>
                        <th>Status</th>
                        <th>Nominal</th>
                    </tr>
                </thead>
                <tbody>

                    <?php $no = 1;
                    while ($row = $members->fetch_assoc()): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['division']) ?></td>

                            <td>
                                <?php if ($row['status'] == 'lunas'): ?>
                                    <span class="badge badge-success">Lunas</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Belum</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <form method="POST" style="display:flex;align-items:center;gap:12px;width:100%;">
                                    <input type="hidden" name="member_id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="month" value="<?= $selectedMonth ?>">
                                    <input type="hidden" name="year" value="<?= $selectedYear ?>">

                                    <span style="flex:1;">
                                        Rp
                                        <?= $row['status'] == 'lunas' ? number_format($row['amount'], 0, ',', '.') : '10.000' ?>
                                    </span>

                                    <?php if ($row['status'] == 'lunas'): ?>
                                        <button name="batal" class="btn btn-danger"
                                            onclick="return confirmBatal()">Batal</button>
                                    <?php else: ?>
                                        <button name="bayar" class="btn btn-success"
                                            onclick="return confirmBayar()">Bayar</button>
                                    <?php endif; ?>

                                </form>
                            </td>

                        </tr>
                    <?php endwhile; ?>

                </tbody>
            </table>
        </div>

    </div>

    <script>
        function confirmBayar() {
            return confirm("Yakin ingin menandai pembayaran Rp 10.000 sebagai LUNAS?");
        }
        function confirmBatal() {
            return confirm("Yakin ingin membatalkan pembayaran ini?");
        }
    </script>

</body>

</html>