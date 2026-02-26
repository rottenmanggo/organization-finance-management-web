<?php
if (!isset($pageTitle)) {
    $pageTitle = "Dashboard";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>
        <?= $pageTitle ?>
    </title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom Style -->
    <style>
        body {
            overflow-x: hidden;
        }

        .sidebar {
            height: 100vh;
            background: #1e293b;
            color: white;
        }

        .sidebar a {
            color: #cbd5e1;
            text-decoration: none;
            display: block;
            padding: 12px 20px;
        }

        .sidebar a:hover {
            background: #334155;
            color: white;
        }

        .active-menu {
            background: #0d6efd;
            color: white !important;
        }

        .topbar {
            background: white;
            border-bottom: 1px solid #ddd;
            padding: 15px;
        }

        .content {
            padding: 25px;
        }
    </style>
</head>

<body>

    <div class="container-fluid">
        <div class="row">

            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-0">
                <h4 class="text-center py-4 border-bottom">GYM SYSTEM</h4>

                <a href="dashboard.php"
                    class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active-menu' : '' ?>">Dashboard</a>
                <a href="members.php"
                    class="<?= basename($_SERVER['PHP_SELF']) == 'members.php' ? 'active-menu' : '' ?>">Members</a>
                <a href="programs.php"
                    class="<?= basename($_SERVER['PHP_SELF']) == 'programs.php' ? 'active-menu' : '' ?>">Programs</a>
                <a href="transactions.php"
                    class="<?= basename($_SERVER['PHP_SELF']) == 'transactions.php' ? 'active-menu' : '' ?>">Transactions</a>
                <a href="reports.php"
                    class="<?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active-menu' : '' ?>">Reports</a>
                <a href="logout.php" class="text-danger mt-3">Logout</a>
            </div>

            <!-- Main Area -->
            <div class="col-md-10 p-0">

                <!-- Topbar -->
                <div class="topbar d-flex justify-content-between">
                    <h5 class="mb-0">
                        <?= $pageTitle ?>
                    </h5>
                    <span>Welcome, Admin</span>
                </div>

                <!-- Content -->
                <div class="content">
                </div>
            </div>
        </div>
    </div>

</body>

</html>