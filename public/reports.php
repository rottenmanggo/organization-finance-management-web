
<?php
session_start();
require_once '../app/config/db.php';
require_once '../app/helpers/auth.php';
require_login();
$data=$pdo->query("SELECT type,SUM(amount) as total FROM transactions GROUP BY type")->fetchAll();
?>
<!DOCTYPE html>
<html><head>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body>
<div class="container mt-4">
<h3>Laporan Ringkas</h3>
<table class="table table-bordered">
<tr><th>Tipe</th><th>Total</th></tr>
<?php foreach($data as $row): ?>
<tr><td><?=$row['type']?></td><td><?=number_format($row['total'],0,',','.')?></td></tr>
<?php endforeach; ?>
</table>
<a href="dashboard.php" class="btn btn-secondary">Kembali</a>
</div></body></html>
