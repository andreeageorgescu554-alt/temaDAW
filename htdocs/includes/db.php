<?php
$DB_HOST = "sqxyz.infinityfree.com";
$DB_USER = "if0_xxxxxx";
$DB_PASS = "parola-ta";
$DB_NAME = "if0_xxxxxxxx_hotel";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    die("Database connection failed");
}
$conn->set_charset("utf8mb4");
