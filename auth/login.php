<?php
session_start();
require_once "../config/database.php";

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
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: "Poppins", sans-serif;
        }

        body {
            background: #1a1a1a;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            position: relative;
            width: 1000px;
            height: 550px;
            background: #fff;
            border-radius: 30px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
        }

        /* ================= FORM ================= */

        .form-container {
            position: absolute;
            top: 0;
            width: 50%;
            height: 100%;
            padding: 90px 40px;
            transition: all 0.6s ease-in-out;
            backface-visibility: hidden;
        }

        .sign-in {
            left: 0;
            z-index: 2;
        }

        .sign-up {
            left: 0;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        /* ACTIVE STATE */

        .container.active .sign-in {
            transform: translateX(100%);
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        .container.active .sign-up {
            transform: translateX(100%);
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
            z-index: 5;
        }

        /* ================= TYPO ================= */

        h1 {
            font-weight: 700;
            font-size: 32px;
            margin-bottom: 20px;
        }

        p {
            font-size: 14px;
            margin-bottom: 15px;
            color: #555;
            text-align: center;
        }

        /* ================= SOCIAL ================= */

        .social {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-bottom: 20px;
        }

        .social div {
            width: 40px;
            height: 40px;
            border: 1px solid #ddd;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
        }

        /* ================= INPUT ================= */

        input {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 10px;
            background: #f1f1f1;
            margin: 10px 0;
        }

        /* ================= BUTTON ================= */

        button {
            margin-top: 15px;
            padding: 14px 50px;
            border: none;
            border-radius: 30px;
<<<<<<< HEAD
            background: #1d7db0;
=======
            background: #1eb0ff;
>>>>>>> 25ec46938720ad8b52700d07bffb3a3d0949c525
            color: #fff;
            font-weight: 600;
            cursor: pointer;
            display: block;
            margin: 20px auto 0 auto;
        }

        button.ghost {
            background: transparent;
            border: 2px solid #fff;
        }

        /* ================= OVERLAY ================= */

        .overlay-container {
            position: absolute;
            top: 0;
            left: 50%;
            width: 50%;
            height: 100%;
            overflow: hidden;
            transition: transform 0.6s ease-in-out;
            z-index: 100;
        }

        .overlay {
<<<<<<< HEAD
            background: #1d7db0;
=======
            background: #1eb0ff;
>>>>>>> 25ec46938720ad8b52700d07bffb3a3d0949c525
            color: #fff;
            position: relative;
            left: -100%;
            width: 200%;
            height: 100%;
            transition: transform 0.6s ease-in-out;
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
            padding: 0 40px;
        }

        .overlay-right {
            right: 0;
        }

        /* MOVE OVERLAY */

        .container.active .overlay-container {
            transform: translateX(-100%);
        }

        .container.active .overlay {
            transform: translateX(50%);
        }
    </style>
</head>

<body>
    <div class="container" id="container">
        <!-- SIGN UP -->
        <div class="form-container sign-up">
            <h1 style="text-align: center">Create Account</h1>
            <p>Register with E-mail</p>

            <input type="text" placeholder="Name" />
            <input type="email" placeholder="Enter E-mail" />
            <input type="password" placeholder="Enter Password" />

            <button>SIGN UP</button>
        </div>

        <!-- SIGN IN -->
        <div class="form-container sign-in">
            <h1 style="text-align: center">Sign In</h1>
            <p style="text-align: center">Sign in with Email & Password</p>

            <?php if (isset($error)): ?>
                <p style="color:red; text-align:center;">
                    <?= $error ?>
                </p>
            <?php endif; ?>

            <form method="POST">
                <input type="text" name="username" placeholder="Username" required />
                <input type="password" name="password" placeholder="Password" required />
                <button type="submit" name="login">SIGN IN</button>
            </form>
        </div>

        <!-- OVERLAY -->
        <div class="overlay-container">
            <div class="overlay">
                <div class="overlay-panel">
                    <h1>Selamat Datang<br />SIMAKAS</h1>
                    <p style="color: #fff">Sign in With Email & Password</p>
                    <button class="ghost" id="signIn">SIGN IN</button>
                </div>

                <div class="overlay-panel overlay-right">
                    <h1>Halo</h1>
                    <p style="color: #fff">Sign up now and enjoy</p>
                    <button class="ghost" id="signUp">SIGN UP</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const container = document.getElementById("container");
        document.getElementById("signUp").onclick = () =>
            container.classList.add("active");
        document.getElementById("signIn").onclick = () =>
            container.classList.remove("active");
    </script>
</body>

</html>
