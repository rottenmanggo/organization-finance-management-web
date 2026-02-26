<?php
$host = "127.0.0.1";
$port = "3307";
$db = "orgfinance";
$user = "root";
$pass = ""; // isi jika ada password

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}
?>