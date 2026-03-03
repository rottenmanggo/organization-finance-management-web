<?php
session_start();

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

if (!isset($_SESSION['show_splash'])) {
    header("Location: dashboard.php");
    exit;
}

unset($_SESSION['show_splash']);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Loading...</title>

    <script>
        setTimeout(function () {
            window.location.replace("dashboard.php");
        }, 2000);
    </script>

    <style>
        body {
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            background: #0d6efd;
            color: white;
            font-family: 'Segoe UI', sans-serif;

            /* Fade animation */
            opacity: 0;
            animation: fadeIn 0.8s ease forwards;
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
            }
        }

        .loader {
            border: 5px solid rgba(255, 255, 255, 0.3);
            border-top: 5px solid white;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin-top: 20px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>

    <h2>Selamat Datang <?= $_SESSION['name'] ?></h2>
    <p>Menyiapkan dashboard...</p>
    <div class="loader"></div>

</body>

</html>