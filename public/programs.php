<?php
require_once '../app/config/db.php';

// Tambah program
if (isset($_POST['add_program'])) {
    $stmt = $pdo->prepare("INSERT INTO programs (name,pic,start_date,end_date,description) VALUES (?,?,?,?,?)");
    $stmt->execute([
        $_POST['name'],
        $_POST['pic'],
        $_POST['start_date'],
        $_POST['end_date'],
        $_POST['description']
    ]);
    header("Location: programs.php");
    exit;
}

$programs = $pdo->query("SELECT * FROM programs")->fetchAll();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Program Kerja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <div class="container mt-4">

        <h3>Program Kerja</h3>

        <div class="card p-3 mb-4">
            <form method="POST">
                <div class="row g-2">
                    <div class="col-md-3"><input type="text" name="name" class="form-control" placeholder="Nama Program"
                            required></div>
                    <div class="col-md-2"><input type="text" name="pic" class="form-control" placeholder="PIC"></div>
                    <div class="col-md-2"><input type="date" name="start_date" class="form-control"></div>
                    <div class="col-md-2"><input type="date" name="end_date" class="form-control"></div>
                    <div class="col-md-3"><input type="text" name="description" class="form-control"
                            placeholder="Deskripsi"></div>
                </div>
                <button name="add_program" class="btn btn-success mt-3">Tambah</button>
            </form>
        </div>

        <table class="table table-bordered">
            <tr>
                <th>Program</th>
                <th>PIC</th>
                <th>Periode</th>
                <th>Total Budget</th>
                <th>Total Realisasi</th>
            </tr>

            <?php foreach ($programs as $p):
                $budget = $pdo->query("SELECT SUM(subtotal) as total FROM budget_items WHERE program_id=" . $p['id'])->fetch()['total'] ?? 0;
                $realisasi = $pdo->query("SELECT SUM(amount) as total FROM transactions WHERE program_id=" . $p['id'] . " AND type='OUT'")->fetch()['total'] ?? 0;
                ?>
                <tr>
                    <td>
                        <?= $p['name'] ?>
                    </td>
                    <td>
                        <?= $p['pic'] ?>
                    </td>
                    <td>
                        <?= $p['start_date'] ?> -
                        <?= $p['end_date'] ?>
                    </td>
                    <td>Rp
                        <?= number_format($budget, 0, ',', '.') ?>
                    </td>
                    <td>
                        Rp
                        <?= number_format($realisasi, 0, ',', '.') ?>
                        <?php if ($realisasi > $budget): ?>
                            <span class="badge bg-danger">Overbudget</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>

        </table>

    </div>
</body>

</html>