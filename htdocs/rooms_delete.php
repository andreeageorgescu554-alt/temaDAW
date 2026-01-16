<?php
require_once "includes/auth.php";
require_role("admin");

require_once "includes/db.php";

$id = (int)($_GET["id"] ?? 0);
if ($id <= 0) die("ID invalid.");

$stmt = $conn->prepare("DELETE FROM rooms WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: rooms_list.php");
    exit;
} else {
    die("Eroare DELETE: " . $conn->error);
}
