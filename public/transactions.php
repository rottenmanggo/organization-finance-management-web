<?php
require_once '../app/config/db.php';

// Tambah transaksi
if (isset($_POST['save'])) {

    $date = $_POST['date'];
    $amount = $_POST['amount'];
    $description = $_POST['description'];

    // generate nomor transaksi otomatis
    $last = $pdo->query("SELECT COUNT(*) as total FROM transactions")->fetch();
    $no = str_pad($last['total'] + 1, 4, '0', STR_PAD_LEFT);
    $trx_no = "TRX-" . date('Ym') . "-" . $no;

    $stmt = $pdo->prepare("INSERT INTO transactions 
        (period_id, trx_no, date, type, amount, description) 
        VALUES (1, ?, ?, 'IN', ?, ?)");

    $stmt->execute([$trx_no, $date, $amount, $description]);

    header("Location: transactions.php");
    exit;
}

$data = $pdo->query("SELECT * FROM transactions ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Transaksi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <div class="container mt-4">

        <h3>Tambah Pemasukan</h3>

        <div class="card p-3 mb-4">
            <form method="POST">
                <div class="row">
                    <div class="col-md-3">
                        <input type="date" name="date" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <input type="number" name="amount" class="form-control" placeholder="Nominal" required>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="description" class="form-control" placeholder="Keterangan" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" name="save" class="btn btn-success w-100">Simpan</button>
                    </div>
                </div>
            </form>
        </div>

        <h4>Daftar Transaksi</h4>
        <table class="table table-bordered">
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Tipe</th>
                <th>Nominal</th>
                <th>Deskripsi</th>
            </tr>

            <?php foreach ($data as $row): ?>
                <tr>
                    <td><?= $row['trx_no'] ?></td>
                    <td><?= $row['date'] ?></td>
                    <td><?= $row['type'] ?></td>
                    <td>Rp <?= number_format($row['amount'], 0, ',', '.') ?></td>
                    <td><?= $row['description'] ?></td>
                </tr>
            <?php endforeach; ?>

        </table>

        <a href="dashboard.php" class="btn btn-secondary">Kembali</a>

    </div>
</body>

</html>