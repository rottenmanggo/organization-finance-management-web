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

/* ===== TAMBAH ===== */
if (isset($_POST['tambah'])) {
    $desc = $_POST['description'];
    $amount = $_POST['amount'];
    $date = $_POST['date'];

    $stmt = $conn->prepare("INSERT INTO income (description, amount, date, created_by) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sdsi", $desc, $amount, $date, $_SESSION['user_id']);
    $stmt->execute();
}

/* ===== UPDATE ===== */
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $desc = $_POST['description'];
    $amount = $_POST['amount'];
    $date = $_POST['date'];

    $stmt = $conn->prepare("UPDATE income SET description=?, amount=?, date=? WHERE id=?");
    $stmt->bind_param("sdsi", $desc, $amount, $date, $id);
    $stmt->execute();
}

/* ===== HAPUS ===== */
if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];
    $conn->query("DELETE FROM income WHERE id=$id");
}

/* ===== DATA ===== */
$data = $conn->query("
    SELECT * FROM income 
    WHERE MONTH(date) = $selectedMonth 
    AND YEAR(date) = $selectedYear
    ORDER BY date DESC
");

$totalQuery = $conn->query("
    SELECT SUM(amount) as total 
    FROM income 
    WHERE MONTH(date) = $selectedMonth 
    AND YEAR(date) = $selectedYear
");

$totalIncome = $totalQuery->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html>

<head>
    <title>Income | SIMAKAS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: #f8fafc;
        }

        .sidebar {
            height: 100vh;
            background: #0f172a;
            position: fixed;
            width: 230px;
            color: white;
        }

        .sidebar a {
            display: block;
            padding: 12px 20px;
            color: #cbd5e1;
            text-decoration: none;
        }

        .sidebar a:hover {
            background: #1e293b;
            color: white;
        }

        .active-menu {
            background: #2563eb !important;
            color: white !important;
        }

        .content {
            margin-left: 230px;
            padding: 30px;
        }

        .card-clean {
            border: 0;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <h5 class="text-center py-3 border-bottom">SIMAKAS</h5>
        <a href="dashboard.php" class="<?= ($currentPage == 'dashboard.php') ? 'active-menu' : '' ?>">Dashboard</a>
        <a href="members.php" class="<?= ($currentPage == 'members.php') ? 'active-menu' : '' ?>">Members</a>
        <a href="kas.php" class="<?= ($currentPage == 'kas.php') ? 'active-menu' : '' ?>">Kas</a>
        <a href="income.php" class="<?= ($currentPage == 'income.php') ? 'active-menu' : '' ?>">Income</a>
        <a href="expense.php" class="<?= ($currentPage == 'expense.php') ? 'active-menu' : '' ?>">Expense</a>
        <a href="auth/logout.php" class="text-danger">Logout</a>
    </div>

    <div class="content">

        <h3 class="fw-bold mb-3">Income</h3>

        <form class="row mb-3" method="GET">
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
                <button class="btn btn-outline-primary">Filter</button>
            </div>
        </form>

        <div class="text-end mb-3">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahModal">
                <i class="bi bi-plus-circle"></i> Tambah Income
            </button>
        </div>

        <div class="card card-clean">
            <div class="card-body">

                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Deskripsi</th>
                            <th>Nominal</th>
                            <th>Tanggal</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $no = 1;
                        while ($row = $data->fetch_assoc()): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= $row['description'] ?></td>
                                <td class="fw-semibold text-success">
                                    Rp <?= number_format($row['amount'], 0, ',', '.') ?>
                                </td>
                                <td><?= $row['date'] ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
                                        data-bs-target="#edit<?= $row['id'] ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>

                                    <a href="?hapus=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Yakin hapus?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>

                            <div class="modal fade" id="edit<?= $row['id'] ?>">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST">
                                            <div class="modal-header">
                                                <h6>Edit Income</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                <input type="text" name="description" class="form-control mb-2"
                                                    value="<?= $row['description'] ?>" required>
                                                <input type="number" name="amount" class="form-control mb-2"
                                                    value="<?= $row['amount'] ?>" required>
                                                <input type="date" name="date" class="form-control"
                                                    value="<?= $row['date'] ?>" required>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" name="update" class="btn btn-primary">Update</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        <?php endwhile; ?>
                    </tbody>
                </table>

                <div class="text-end mt-3">
                    <h5>Total Bulan Ini:
                        <span class="text-success fw-bold">
                            Rp <?= number_format($totalIncome, 0, ',', '.') ?>
                        </span>
                    </h5>
                </div>

            </div>
        </div>

    </div>

    <div class="modal fade" id="tambahModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h6>Tambah Income</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="text" name="description" class="form-control mb-2"
                            placeholder="Deskripsi pemasukan" required>
                        <input type="number" name="amount" class="form-control mb-2" placeholder="Nominal" required>
                        <input type="date" name="date" class="form-control" required>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="tambah" class="btn btn-primary">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>