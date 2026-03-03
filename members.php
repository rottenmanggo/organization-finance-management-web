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

$currentPage = basename($_SERVER['PHP_SELF']);

// ================= TAMBAH MEMBER =================
if (isset($_POST['tambah'])) {
    $stmt = $conn->prepare("INSERT INTO members (name, division, angkatan, phone) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $_POST['name'], $_POST['division'], $_POST['angkatan'], $_POST['phone']);
    $stmt->execute();
}

// ================= HAPUS =================
if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];
    $conn->query("DELETE FROM members WHERE id=$id");
}

$result = $conn->query("SELECT * FROM members ORDER BY id DESC");
?>

<!doctype html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Members | SIMAKAS</title>
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
            <h1>Members</h1>
            <button class="btn btn-primary" onclick="openModal()">+ Tambah Member</button>
        </div>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Divisi</th>
                        <th>Angkatan</th>
                        <th>HP</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>

                    <?php $no = 1;
                    while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['division']) ?></td>
                            <td><?= htmlspecialchars($row['angkatan']) ?></td>
                            <td><?= htmlspecialchars($row['phone']) ?></td>
                            <td>
                                <?php if ($row['status'] == "aktif"): ?>
                                    <span class="badge badge-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="?hapus=<?= $row['id'] ?>" class="btn btn-danger"
                                    onclick="return confirm('Yakin hapus?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>

                </tbody>
            </table>
        </div>

    </div>

    <!-- MODAL -->
    <div class="modal" id="modal">
        <div class="modal-content">
            <h3>Tambah Member</h3>
            <form method="POST">
                <input type="text" name="name" placeholder="Nama" required>
                <input type="text" name="division" placeholder="Divisi" required>
                <input type="text" name="angkatan" placeholder="Angkatan" required>
                <input type="text" name="phone" placeholder="No HP" required>
                <button type="submit" name="tambah" class="btn btn-primary">Simpan</button>
                <button type="button" onclick="closeModal()" class="btn btn-danger">Batal</button>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById("modal").style.display = "flex"
        }
        function closeModal() {
            document.getElementById("modal").style.display = "none"
        }
    </script>

</body>

</html>