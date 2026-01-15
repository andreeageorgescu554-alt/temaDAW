<?php
session_start();
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Hotel - Acasă</title>
</head>
<body>

<h2>Hotel - Pagina principală</h2>

<?php if (!isset($_SESSION["user_id"])): ?>
    <p>Nu ești autentificat.</p>
    <p><a href="login.php">Login</a> | <a href="register.php">Înregistrare</a></p>
<?php else: ?>
    <p>Salut, <b><?php echo htmlspecialchars($_SESSION["username"]); ?></b>!</p>
    <p>Rol: <b><?php echo htmlspecialchars($_SESSION["role"]); ?></b></p>

    <p>
        <a href="logout.php">Logout</a>
    </p>

     <?php if ($_SESSION["role"] === "admin"): ?>
        <hr>
        <h3>Meniu Admin</h3>
        <ul>
            <li><a href="admin.php">Admin dashboard</a></li>
            <li><a href="rooms_list.php">Gestionare camere</a></li>
            <li><a href="reservations_list.php">Gestionare rezervări</a></li>
            <li><a href="messages_list.php">Mesaje contact</a></li>
        </ul>

    <?php elseif ($_SESSION["role"] === "receptioner"): ?>
        <hr>
        <h3>Meniu Recepționer</h3>
        <ul>
            <li><a href="receptioner.php">Dashboard recepționer</a></li>
            <li><a href="reservations_list.php">Gestionare rezervări</a></li>
            <li><a href="reservations_add.php">Rezervare nouă</a></li>
        </ul>

    <?php elseif ($_SESSION["role"] === "client"): ?>
        <hr>
        <h3>Meniu Client</h3>
        <ul>
            <li><a href="client.php">Dashboard client</a></li>
            <li><a href="reservations_list.php">Rezervările mele</a></li>
            <li><a href="reservations_add.php">Rezervare nouă</a></li>
        </ul>

    <?php endif; ?>

<?php endif; ?>

</body>
</html>