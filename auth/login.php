<?php
session_start();
require_once "../config/database.php";

/* ================= LOGIN ================= */
if (isset($_POST['login'])) {

    $username = htmlspecialchars($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {

        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            header("Location: ../dashboard.php");
            exit;

        } else {
            $error = "Password salah!";
        }

    } else {
        $error = "Username tidak ditemukan!";
    }
}

/* ================= REGISTER ================= */
if (isset($_POST['register'])) {

    $username = htmlspecialchars($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = "VIEWER";

    $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $error = "Username sudah digunakan!";
    } else {

        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $password, $role);

        if ($stmt->execute()) {
            $success = "Registrasi berhasil! Silakan login.";
        } else {
            $error = "Terjadi kesalahan!";
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Login SIMAKAS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #1c1c1c;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container-box {
            position: relative;
            width: 800px;
            height: 480px;
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        }

        .form-container {
            position: absolute;
            top: 0;
            height: 100%;
            width: 50%;
            padding: 50px;
            transition: all .6s ease-in-out;
        }

        .sign-in {
            left: 0;
            z-index: 2;
        }

        .sign-up {
            left: 0;
            opacity: 0;
            z-index: 1;
        }

        .overlay-container {
            position: absolute;
            top: 0;
            left: 50%;
            width: 50%;
            height: 100%;
            overflow: hidden;
            transition: transform .6s ease-in-out;
            z-index: 100;
        }

        .overlay {
            background: #0d6efd;
            color: white;
            position: relative;
            left: -100%;
            width: 200%;
            height: 100%;
            transition: transform .6s ease-in-out;
        }

        .overlay-panel {
            position: absolute;
            width: 50%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 40px;
        }

        .overlay-right {
            right: 0;
        }

        .container-box.active .sign-in {
            transform: translateX(100%);
        }

        .container-box.active .sign-up {
            transform: translateX(100%);
            opacity: 1;
            z-index: 5;
        }

        .container-box.active .overlay-container {
            transform: translateX(-100%);
        }

        .container-box.active .overlay {
            transform: translateX(50%);
        }
    </style>
</head>

<body>

    <div class="container-box" id="container">

        <!-- REGISTER -->
        <div class="form-container sign-up">
            <h3 class="mb-4 text-center">Register SIMAKAS</h3>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="text" name="username" class="form-control mb-3" placeholder="Username" required>
                <input type="password" name="password" class="form-control mb-3" placeholder="Password" required>
                <button name="register" class="btn btn-primary w-100">Register</button>
            </form>
        </div>

        <!-- LOGIN -->
        <div class="form-container sign-in">
            <h3 class="mb-4 text-center">Login SIMAKAS</h3>

            <?php if (isset($error) && !isset($_POST['register'])): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="text" name="username" class="form-control mb-3" placeholder="Username" required>
                <input type="password" name="password" class="form-control mb-3" placeholder="Password" required>
                <button name="login" class="btn btn-primary w-100">Login</button>
            </form>
        </div>

        <!-- OVERLAY -->
        <div class="overlay-container">
            <div class="overlay">

                <div class="overlay-panel">
                    <h2>Welcome Back!</h2>
                    <p>Sudah punya akun?</p>
                    <button class="btn btn-light" id="signIn">Login</button>
                </div>

                <div class="overlay-panel overlay-right">
                    <h2>Halo!</h2>
                    <p>Belum punya akun?</p>
                    <button class="btn btn-light" id="signUp">Register</button>
                </div>

            </div>
        </div>

    </div>

    <script>
        const container = document.getElementById('container');
        document.getElementById('signUp').onclick = () => container.classList.add("active");
        document.getElementById('signIn').onclick = () => container.classList.remove("active");
    </script>

</body>

</html>