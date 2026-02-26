<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Dashboard SIMAKAS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <span class="navbar-brand">SIMAKAS</span>
            <span class="text-white">
                <?= $_SESSION['username'] ?> (
                <?= $_SESSION['role'] ?>)
                | <a href="auth/logout.php" class="text-danger">Logout</a>
            </span>
        </div>
    </nav>

    <div class="container mt-4">
        <h3>Dashboard</h3>
        <p>Selamat datang di sistem manajemen kas organisasi.</p>
    </div>

</body>

</html>