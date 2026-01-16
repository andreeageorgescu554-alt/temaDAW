<?php
require_once "includes/auth.php";
require_role("receptioner");
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Recepționer</title>
    <style>
body {
    margin: 0;
    min-height: 100vh;
    background-image: url("assets/lobby.jpg");
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed;
    font-family: Arial, sans-serif;
}
</style>

</head>
<body>
<div style="
    background: rgba(255,255,255,0.9);
    max-width: 900px;
    margin: 40px auto;
    padding: 20px;
    border-radius: 8px;
">
<h2>Recepționer - Dashboard</h2>
<p>Salut, <b><?php echo htmlspecialchars($_SESSION["username"]); ?></b>!</p>

<ul>
    <li><a href="reservations_list.php">Vezi rezervări</a></li>
    <li><a href="reservations_add.php">Creează rezervare</a></li>
    <li><a href="rooms_list.php">Camere (doar dacă ești admin)</a></li>
    <li><a href="index.php">Înapoi la index</a></li>
</ul>

<p><a href="logout.php">Logout</a></p>
    </div>
</body>
</html>
