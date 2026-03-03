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

$selectedMonth = $_GET['month'] ?? date('n');
$selectedYear = $_GET['year'] ?? date('Y');

/* ================= BATAL ================= */
if (isset($_POST['batal'])) {

    $member_id = $_POST['member_id'];
    $month = $_POST['month'];
    $year = $_POST['year'];

    // Ambil income_id dulu
    $getKas = $conn->prepare("SELECT income_id FROM kas WHERE member_id=? AND month=? AND year=?");
    $getKas->bind_param("iii", $member_id, $month, $year);
    $getKas->execute();
    $result = $getKas->get_result()->fetch_assoc();

    if ($result && $result['income_id']) {

        // Hapus dari income
        $deleteIncome = $conn->prepare("DELETE FROM income WHERE id=?");
        $deleteIncome->bind_param("i", $result['income_id']);
        $deleteIncome->execute();
    }

    // Update kas
    $stmt = $conn->prepare("
        UPDATE kas 
        SET status='belum', amount=NULL, income_id=NULL 
        WHERE member_id=? AND month=? AND year=?
    ");
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

    // Cek apakah sudah ada record kas
    $check = $conn->prepare("SELECT id, status FROM kas WHERE member_id=? AND month=? AND year=?");
    $check->bind_param("iii", $member_id, $month, $year);
    $check->execute();
    $resultCheck = $check->get_result();

    if ($resultCheck->num_rows > 0) {

        $row = $resultCheck->fetch_assoc();

        if ($row['status'] == 'lunas') {
            header("Location: kas.php?month=$month&year=$year");
            exit;
        }

        // Insert ke income
        $desc = "Kas Bulanan - Member ID $member_id ($month/$year)";
        $date = date("Y-m-d");

        $incomeStmt = $conn->prepare("
            INSERT INTO income (description, amount, date) 
            VALUES (?, ?, ?)
        ");
        $incomeStmt->bind_param("sds", $desc, $amount, $date);
        $incomeStmt->execute();

        $income_id = $incomeStmt->insert_id;

        // Update kas
        $update = $conn->prepare("
            UPDATE kas 
            SET amount=?, status='lunas', income_id=? 
            WHERE member_id=? AND month=? AND year=?
        ");
        $update->bind_param("diiii", $amount, $income_id, $member_id, $month, $year);
        $update->execute();

    } else {

        // Insert ke income dulu
        $desc = "Kas Bulanan - Member ID $member_id ($month/$year)";
        $date = date("Y-m-d");

        $incomeStmt = $conn->prepare("
            INSERT INTO income (description, amount, date) 
            VALUES (?, ?, ?)
        ");
        $incomeStmt->bind_param("sds", $desc, $amount, $date);
        $incomeStmt->execute();

        $income_id = $incomeStmt->insert_id;

        // Insert ke kas
        $insertKas = $conn->prepare("
            INSERT INTO kas (member_id, month, year, amount, status, income_id) 
            VALUES (?, ?, ?, ?, 'lunas', ?)
        ");
        $insertKas->bind_param("iiidi", $member_id, $month, $year, $amount, $income_id);
        $insertKas->execute();
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