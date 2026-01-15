<?php
$DB_HOST = "sql301.infinityfree.com";
$DB_USER = "if0_40906823";
$DB_PASS = "MYAthoMJxZkD";
$DB_NAME = "if0_40906823_hotel";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    die("Database connection failed");
}
$conn->set_charset("utf8mb4");
