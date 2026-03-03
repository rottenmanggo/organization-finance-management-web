<?php
$host = "localhost:3306";
$user = "root";
$pass = "";
$db = "db_kasorg";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>