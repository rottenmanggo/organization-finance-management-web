<?php
require_once "config/database.php";

header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename=transactions.csv');

$output = fopen("php://output", "w");

/* Header kolom */
fputcsv($output, ['Date', 'Description', 'Type', 'Amount']);

/* Ambil data */
$query = $conn->query("
    SELECT date, description, amount, 'Income' as type FROM income
    UNION ALL
    SELECT date, description, amount, 'Expense' as type FROM expense
    ORDER BY date DESC
");

while ($row = $query->fetch_assoc()) {
    fputcsv($output, [
        $row['date'],
        $row['description'],
        $row['type'],
        $row['amount']
    ]);
}

fclose($output);
exit;