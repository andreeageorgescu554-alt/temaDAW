<?php
require_once "includes/auth.php";
require_login();
require_once "includes/db.php";

$role = $_SESSION["role"];
$userId = (int)$_SESSION["user_id"];

// Header CSV (download)
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=rezervari.csv');

// deschidem output stream
$output = fopen('php://output', 'w');

// header CSV
fputcsv($output, [
    'User',
    'Camera',
    'Start date',
    'End date',
    'Status',
    'Total price'
]);

if ($role === "admin" || $role === "receptioner") {
    $sql = "SELECT u.username, ro.room_number, r.start_date, r.end_date, r.status, r.total_price
            FROM reservations r
            JOIN users u ON u.id = r.user_id
            JOIN rooms ro ON ro.id = r.room_id
            ORDER BY r.created_at DESC";
    $stmt = $conn->prepare($sql);
} else {
    $sql = "SELECT u.username, ro.room_number, r.start_date, r.end_date, r.status, r.total_price
            FROM reservations r
            JOIN users u ON u.id = r.user_id
            JOIN rooms ro ON ro.id = r.room_id
            WHERE r.user_id = ?
            ORDER BY r.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
}

$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
    fputcsv($output, [
        $row['username'],
        $row['room_number'],
        $row['start_date'],
        $row['end_date'],
        $row['status'],
        $row['total_price']
    ]);
}

$stmt->close();
fclose($output);
exit;
