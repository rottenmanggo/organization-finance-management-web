<?php
require_once '../app/config/db.php';

$in = $pdo->query("SELECT SUM(amount) as total FROM transactions WHERE type='IN'")
    ->fetch()['total'] ?? 0;

$out = $pdo->query("SELECT SUM(amount) as total FROM transactions WHERE type='OUT'")
    ->fetch()['total'] ?? 0;

$saldo = $in - $out;

$transaksi = $pdo->query("
    SELECT * FROM transactions 
    ORDER BY id DESC 
    LIMIT 5
")->fetchAll();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Dashboard - OrgFinance</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8fafc;
        }

        .stat-card {
            border-radius: 16px;
            padding: 25px;
            color: white;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.06);
        }

        .card-green {
            background: linear-gradient(135deg, #16a34a, #22c55e);
        }

        .card-red {
            background: linear-gradient(135deg, #dc2626, #ef4444);
        }

        .card-blue {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
        }

        .section-card {
            border-radius: 16px;
            border: none;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05);
        }

        .btn-rounded {
            border-radius: 50px;
            padding: 8px 18px;
        }

        table th {
            font-weight: 600;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <span class="navbar-brand fw-bold">OrgFinance</span>
            <div>
                <a href="transactions.php" class="btn btn-light btn-sm btn-rounded">Transaksi</a>
                <a href="members.php" class="btn btn-outline-light btn-sm btn-rounded">Anggota</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold">Dashboard</h3>
            <div>
                <a href="transactions.php" class="btn btn-success btn-rounded me-2">+ Tambah Pemasukan</a>
                <a href="members.php" class="btn btn-primary btn-rounded">Kelola Kas Anggota</a>
            </div>
        </div>

        <div class="row g-4 mb-4">

            <div class="col-md-4">
                <div class="stat-card card-green">
                    <h6>Total Pemasukan</h6>
                    <h3 class="fw-bold">Rp <?= number_format($in, 0, ',', '.') ?></h3>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card card-red">
                    <h6>Total Pengeluaran</h6>
                    <h3 class="fw-bold">Rp <?= number_format($out, 0, ',', '.') ?></h3>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card card-blue">
                    <h6>Saldo Kas</h6>
                    <h3 class="fw-bold">Rp <?= number_format($saldo, 0, ',', '.') ?></h3>
                </div>
            </div>

        </div>



        <div class="card section-card p-4">
            <h5 class="fw-semibold mb-3">Transaksi Terbaru</h5>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Tipe</th>
                            <th>Nominal</th>
                            <th>Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transaksi as $row): ?>
                            <tr>
                                <td class="fw-semibold"><?= $row['trx_no'] ?></td>
                                <td><?= $row['date'] ?></td>
                                <td>
                                    <?php if ($row['type'] == 'IN'): ?>
                                        <span class="badge bg-success">IN</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">OUT</span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-semibold">Rp <?= number_format($row['amount'], 0, ',', '.') ?></td>
                                <td><?= $row['description'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>

    </div>

</body>

</html>