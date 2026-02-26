<?php
session_start();
require_once "config/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

$currentPage = basename($_SERVER['PHP_SELF']);

// ================= TAMBAH MEMBER =================
if (isset($_POST['tambah'])) {
    $name = $_POST['name'];
    $division = $_POST['division'];
    $angkatan = $_POST['angkatan'];
    $phone = $_POST['phone'];

    $stmt = $conn->prepare("INSERT INTO members (name, division, angkatan, phone) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $division, $angkatan, $phone);
    $stmt->execute();
}

// ================= UPDATE MEMBER =================
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $division = $_POST['division'];
    $angkatan = $_POST['angkatan'];
    $phone = $_POST['phone'];

    $stmt = $conn->prepare("UPDATE members SET name=?, division=?, angkatan=?, phone=? WHERE id=?");
    $stmt->bind_param("ssssi", $name, $division, $angkatan, $phone, $id);
    $stmt->execute();
}

// ================= HAPUS =================
if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];
    $conn->query("DELETE FROM members WHERE id=$id");
}

// ================= UPDATE STATUS =================
if (isset($_GET['nonaktif'])) {
    $id = (int) $_GET['nonaktif'];
    $conn->query("UPDATE members SET status='nonaktif' WHERE id=$id");
}

if (isset($_GET['aktif'])) {
    $id = (int) $_GET['aktif'];
    $conn->query("UPDATE members SET status='aktif' WHERE id=$id");
}

// ================= AMBIL DATA =================
$result = $conn->query("SELECT * FROM members ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Members | SIMAKAS</title>
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

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Members</h3>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahModal">
                <i class="bi bi-plus-circle"></i> Tambah Member
            </button>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Divisi</th>
                            <th>Angkatan</th>
                            <th>HP</th>
                            <th>Status</th>
                            <th width="200">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1;
                        while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= $row['name']; ?></td>
                                <td><?= $row['division']; ?></td>
                                <td><?= $row['angkatan']; ?></td>
                                <td><?= $row['phone']; ?></td>
                                <td>
                                    <?php if ($row['status'] == 'aktif'): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-info btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#editModal<?= $row['id']; ?>">
                                        Edit
                                    </button>

                                    <?php if ($row['status'] == 'aktif'): ?>
                                        <a href="?nonaktif=<?= $row['id']; ?>" class="btn btn-warning btn-sm">Nonaktif</a>
                                    <?php else: ?>
                                        <a href="?aktif=<?= $row['id']; ?>" class="btn btn-success btn-sm">Aktif</a>
                                    <?php endif; ?>

                                    <a href="?hapus=<?= $row['id']; ?>" class="btn btn-danger btn-sm"
                                        onclick="return confirm('Yakin hapus?')">
                                        Hapus
                                    </a>
                                </td>
                            </tr>

                            <!-- MODAL EDIT -->
                            <div class="modal fade" id="editModal<?= $row['id']; ?>">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST">
                                            <div class="modal-header">
                                                <h5>Edit Member</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="id" value="<?= $row['id']; ?>">
                                                <input type="text" name="name" class="form-control mb-2"
                                                    value="<?= $row['name']; ?>" required>
                                                <input type="text" name="division" class="form-control mb-2"
                                                    value="<?= $row['division']; ?>" required>
                                                <input type="text" name="angkatan" class="form-control mb-2"
                                                    value="<?= $row['angkatan']; ?>" required>
                                                <input type="text" name="phone" class="form-control mb-2"
                                                    value="<?= $row['phone']; ?>" required>
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
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>