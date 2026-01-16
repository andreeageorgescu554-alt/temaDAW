<?php
require_once "includes/auth.php";
require_login();
require_once "includes/db.php";

$role = $_SESSION["role"];
$userId = (int)$_SESSION["user_id"];

$id = (int)($_GET["id"] ?? 0);
if ($id <= 0) die("ID invalid.");

// luăm rezervarea ca să verificăm drepturile la client
$stmt = $conn->prepare("SELECT user_id FROM reservations WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$row) die("Rezervarea nu există.");

if ($role === "client" && (int)$row["user_id"] !== $userId) {
    die("Access denied.");
}

// ștergere
$stmt = $conn->prepare("DELETE FROM reservations WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: reservations_list.php");
    exit;
} else {
    die("Eroare DELETE: " . $conn->error);
}
