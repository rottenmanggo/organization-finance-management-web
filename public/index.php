<?php
session_start();
require_once '../app/config/db.php';

if (isset($_POST['login'])) {

    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username=?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $password == $user['password']) {
        $_SESSION['user'] = $user;
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Login gagal!";
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5 col-md-4">
        <div class="card p-4">
            <h4>OrgFinance Login</h4>
            <?php if (isset($error))
                echo "<div class='alert alert-danger'>$error</div>"; ?>
            <form method="POST">
                <input class="form-control mb-2" name="username" placeholder="Username" required>
                <input class="form-control mb-2" name="password" type="password" placeholder="Password" required>
                <button name="login" class="btn btn-primary w-100">Login</button>
            </form>
        </div>
    </div>
</body>

</html>