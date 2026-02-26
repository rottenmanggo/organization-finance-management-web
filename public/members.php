<?php
require_once '../app/config/db.php';

// ===== FILTER =====
$selected_year = $_GET['year'] ?? date('Y');
$selected_month = $_GET['month'] ?? date('m');

// ===== TAMBAH ANGGOTA =====
if (isset($_POST['add'])) {
    $stmt = $pdo->prepare("INSERT INTO members (name, division, saldo) VALUES (?,?,?)");
    $stmt->execute([
        $_POST['name'],
        $_POST['division'],
        $_POST['saldo']
    ]);
    header("Location: members.php");
    exit;
}

// ===== AMBIL SEMUA MEMBER =====
$members = $pdo->query("SELECT * FROM members ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Kas Anggota</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f4f6f9;
        }

        .card-custom {
            border-radius: 15px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05);
        }
    </style>

</head>

<body>

    <div class="container mt-4">

        <h3 class="mb-3">Kas Anggota</h3>

        <!-- FILTER -->
        <div class="card card-custom p-3 mb-4">
            <form method="GET">
                <div class="row g-2 align-items-end">

                    <div class="col-md-2">
                        <label>Tahun</label>
                        <select name="year" class="form-control">
                            <?php for ($y = date('Y') - 5; $y <= date('Y') + 1; $y++): ?>
                                <option value="<?= $y ?>" <?= $selected_year == $y ? 'selected' : '' ?>>
                                    <?= $y ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label>Bulan</label>
                        <select name="month" class="form-control">
                            <?php for ($m = 1; $m <= 12; $m++):
                                $val = str_pad($m, 2, '0', STR_PAD_LEFT);
                                ?>
                                <option value="<?= $val ?>" <?= $selected_month == $val ? 'selected' : '' ?>>
                                    <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <button class="btn btn-primary w-100">Filter</button>
                    </div>

                </div>
            </form>
        </div>

        <!-- TAMBAH ANGGOTA -->
        <div class="card card-custom p-3 mb-4">
            <form method="POST">
                <div class="row g-2">
                    <div class="col-md-4">
                        <input type="text" name="name" class="form-control" placeholder="Nama Anggota" required>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="division" class="form-control" placeholder="Divisi">
                    </div>
                    <div class="col-md-3">
                        <input type="number" name="saldo" class="form-control" placeholder="Saldo Awal" required>
                    </div>
                    <div class="col-md-2">
                        <button name="add" class="btn btn-success w-100">Tambah</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- TABEL MEMBER -->
        <div class="card card-custom p-4">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nama</th>
                        <th>Divisi</th>
                        <th>Total Saldo</th>
                    </tr>
                </thead>
                <tbody>

                    <?php foreach ($members as $m): ?>

                        <tr>
                            <td><?= htmlspecialchars($m['name']) ?></td>
                            <td><?= htmlspecialchars($m['division']) ?></td>
                            <td class="fw-bold text-primary">
                                Rp <?= number_format($m['saldo'], 0, ',', '.') ?>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                </tbody>
            </table>
        </div>

    </div>

</body>

</html>