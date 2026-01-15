<?php
require_once "includes/auth.php";
require_role("admin");

require_once "includes/db.php";

$sql = "SELECT id, room_number, type, price, capacity, status FROM rooms ORDER BY room_number";
$res = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Camere - Admin</title>
</head>
<body>

<h2>Gestionare camere</h2>
<p><a href="admin.php">Înapoi</a> | <a href="logout.php">Logout</a></p>

<p><a href="rooms_add.php">+ Adaugă cameră</a></p>

<table border="1" cellpadding="6" cellspacing="0">
    <tr>
        <th>Număr</th>
        <th>Tip</th>
        <th>Preț</th>
        <th>Capacitate</th>
        <th>Status</th>
        <th>Acțiuni</th>
    </tr>

    <?php if ($res && $res->num_rows > 0): ?>
        <?php while ($r = $res->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($r["room_number"]); ?></td>
                <td><?php echo htmlspecialchars($r["type"]); ?></td>
                <td><?php echo htmlspecialchars($r["price"]); ?></td>
                <td><?php echo htmlspecialchars($r["capacity"]); ?></td>
                <td><?php echo htmlspecialchars($r["status"]); ?></td>
                <td>
                    <a href="rooms_edit.php?id=<?php echo (int)$r["id"]; ?>">Edit</a> |
                    <a href="rooms_delete.php?id=<?php echo (int)$r["id"]; ?>"
                       onclick="return confirm('Sigur ștergi camera?');">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="6">Nu există camere.</td></tr>
    <?php endif; ?>
</table>

</body>
</html>
