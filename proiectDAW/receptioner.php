<?php
require_once "includes/auth.php";
require_role("receptioner");
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Recepționer</title>
</head>
<body>

<h2>Recepționer - Dashboard</h2>
<p>Salut, <b><?php echo htmlspecialchars($_SESSION["username"]); ?></b>!</p>

<ul>
    <li><a href="reservations_list.php">Vezi rezervări</a></li>
    <li><a href="reservations_add.php">Creează rezervare</a></li>
    <li><a href="rooms_list.php">Camere (doar dacă ești admin)</a></li>
    <li><a href="index.php">Înapoi la index</a></li>
</ul>

<p><a href="logout.php">Logout</a></p>

</body>
</html>
