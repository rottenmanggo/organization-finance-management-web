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
            display: flex;
            justify-content: space-between;
            align-items: center;
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

        /* BUTTON */
        .btn {
            padding: 8px 14px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-primary {
            background: #007bff;
            color: #fff
        }

        .btn-danger {
            background: #dc3545;
            color: #fff
        }

        .btn-warning {
            background: #ffc107;
            color: #000
        }

        .btn-success {
            background: #28a745;
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
            text-align: left;
            font-size: 14px
        }

        th {
            background: #f1f1f1
        }

        tr:not(:last-child) {
            border-bottom: 1px solid #eee
        }

        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
        }

        .badge-success {
            background: #d4edda;
            color: #155724
        }

        .badge-secondary {
            background: #e2e3e5;
            color: #383d41
        }

        /* FORM MODAL SIMPLE */
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

        .modal input {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <div>
            <h2>SIMAKAS</h2>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="members.php" class="active">Members</a></li>
                <li><a href="kas.php">Kas</a></li>
                <li><a href="transactions.php">Transactions</a></li>
            </ul>
        </div>
        <div>
            <ul>
                <li><a href="auth/logout.php" class="logout">Logout</a></li>
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