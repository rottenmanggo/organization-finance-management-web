<?php
session_start();
require_once "config/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

/* ================= TAMBAH ================= */
if (isset($_POST['tambah'])) {

    $type = $_POST['type'];
    $desc = $_POST['description'];
    $amount = $_POST['amount'];
    $date = $_POST['date'];

    if ($type == "Income") {
        $stmt = $conn->prepare("INSERT INTO income (description, amount, date) VALUES (?, ?, ?)");
    } else {
        $stmt = $conn->prepare("INSERT INTO expense (description, amount, date) VALUES (?, ?, ?)");
    }

    $stmt->bind_param("sds", $desc, $amount, $date);
    $stmt->execute();
}

/* ================= HAPUS ================= */
if (isset($_GET['hapus']) && isset($_GET['type'])) {

    $id = (int) $_GET['hapus'];
    $type = $_GET['type'];

    if ($type == "Income") {
        $conn->query("DELETE FROM income WHERE id=$id");
    } else {
        $conn->query("DELETE FROM expense WHERE id=$id");
    }
}

/* ================= DATA ================= */
$data = $conn->query("
    SELECT id, date, description, amount, 'Income' as type FROM income
    UNION ALL
    SELECT id, date, description, amount, 'Expense' as type FROM expense
    ORDER BY date DESC
");

$totalIncome = $conn->query("SELECT SUM(amount) as total FROM income")
    ->fetch_assoc()['total'] ?? 0;

$totalExpense = $conn->query("SELECT SUM(amount) as total FROM expense")
    ->fetch_assoc()['total'] ?? 0;

$saldo = $totalIncome - $totalExpense;
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMAKAS Transactions</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif
        }

        body {
            display: flex;
            background: #f4f6f9
        }

        /* SIDEBAR */
        .sidebar {
            width: 240px;
            height: 100vh;
            background: #1e1e2f;
            color: #fff;
            padding: 30px 20px;
            position: fixed;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .sidebar h2 {
            margin-bottom: 40px
        }

        .sidebar ul {
            list-style: none
        }

        .sidebar ul li {
            margin: 15px 0
        }

        .sidebar ul li a {
            color: #ccc;
            text-decoration: none;
            display: block;
            padding: 8px 10px;
            border-radius: 8px;
            transition: .3s;
            font-size: 14px;
        }

        .sidebar ul li a:hover,
        .sidebar ul li a.active {
            background: #007bff;
            color: #fff
        }

        .logout {
            color: #ff4d4d
        }

        /* MAIN */
        .main {
            margin-left: 240px;
            padding: 30px;
            width: 100%
        }

        .topbar {
            margin-bottom: 30px
        }

        .topbar h1 {
            font-size: 22px
        }

        /* CARDS */
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, .05);
            transition: .3s;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, .08);
        }

        .card h3 {
            font-size: 14px;
            color: #888
        }

        .card h2 {
            margin-top: 10px
        }

        /* FORM */
        .form-input {
            padding: 10px 14px;
            border: 1px solid #ddd;
            border-radius: 8px;
            outline: none;
            font-size: 14px;
            background: #fff;
            min-width: 150px;
        }

        .form-input:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, .1);
        }

        /* BUTTON */
        .btn-primary {
            padding: 10px 18px;
            border: none;
            border-radius: 8px;
            background: #007bff;
            color: #fff;
            cursor: pointer;
            font-size: 14px;
            transition: .3s;
        }

        .btn-primary:hover {
            background: #0056b3;
        }

        .btn-danger {
            padding: 6px 12px;
            border: none;
            border-radius: 8px;
            background: #dc3545;
            color: #fff;
            cursor: pointer;
        }

        /* TABLE */
        .table-container {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, .05);
            margin-top: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse
        }

        th,
        td {
            padding: 12px;
            font-size: 14px
        }

        th {
            background: #f1f1f1
        }

        tr:not(:last-child) {
            border-bottom: 1px solid #eee
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <div>
            <h2>SIMAKAS</h2>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="members.php">Members</a></li>
                <li><a href="kas.php">Kas</a></li>
                <li><a href="transactions.php" class="active">Transactions</a></li>
            </ul>
        </div>
        <div>
            <ul>
                <li><a href="auth/logout.php" class="logout">Logout</a></li>
            </ul>
        </div>
    </div>

    <div class="main">

        <div class="topbar">
            <h1>Transactions</h1>
        </div>

        <!-- SUMMARY -->
        <div class="cards">
            <div class="card">
                <h3>Total Income</h3>
                <h2 style="color:#28a745">Rp <?= number_format($totalIncome, 0, ',', '.') ?></h2>
            </div>

            <div class="card">
                <h3>Total Expense</h3>
                <h2 style="color:#dc3545">Rp <?= number_format($totalExpense, 0, ',', '.') ?></h2>
            </div>

            <div class="card">
                <h3>Saldo</h3>
                <h2 style="color:#007bff">Rp <?= number_format($saldo, 0, ',', '.') ?></h2>
            </div>
        </div>

        <!-- FORM TAMBAH -->
        <div style="margin-bottom:20px;">
            <form method="POST" style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;">

                <select name="type" required class="form-input">
                    <option value="">Type</option>
                    <option value="Income">Income</option>
                    <option value="Expense">Expense</option>
                </select>

                <input type="text" name="description" placeholder="Description" required class="form-input">

                <input type="number" name="amount" placeholder="Amount" required class="form-input">

                <input type="date" name="date" required class="form-input">

                <button type="submit" name="tambah" class="btn-primary">Tambah</button>

            </form>
        </div>

        <!-- TABLE -->
        <div class="table-container">
            <table>
                <tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Aksi</th>
                </tr>

                <?php while ($row = $data->fetch_assoc()): ?>
                    <tr>
                        <td><?= date("d-m-Y", strtotime($row['date'])) ?></td>
                        <td><?= htmlspecialchars($row['description']) ?></td>
                        <td style="color:<?= $row['type'] == 'Income' ? '#28a745' : '#dc3545' ?>;font-weight:600">
                            <?= $row['type'] ?>
                        </td>
                        <td>Rp <?= number_format($row['amount'], 0, ',', '.') ?></td>
                        <td>
                            <a href="?hapus=<?= $row['id'] ?>&type=<?= $row['type'] ?>" class="btn-danger"
                                onclick="return confirm('Yakin hapus?')">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; ?>

            </table>
        </div>

    </div>

</body>

</html>