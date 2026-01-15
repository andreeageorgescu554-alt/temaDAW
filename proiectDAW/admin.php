<?php
require_once "includes/auth.php";
require_role("admin");
require_once "includes/db.php";

// STATISTICI

// total camere
$row = $conn->query("SELECT COUNT(*) AS c FROM rooms")->fetch_assoc();
$totalRooms = (int)($row["c"] ?? 0);

// total rezervări
$row = $conn->query("SELECT COUNT(*) AS c FROM reservations")->fetch_assoc();
$totalReservations = (int)($row["c"] ?? 0);

// venit total (doar rezervări valide)
$row = $conn->query("
    SELECT COALESCE(SUM(total_price),0) AS s
    FROM reservations
    WHERE status IN ('confirmed','checked_in','checked_out')
")->fetch_assoc();
$totalRevenue = (float)($row["s"] ?? 0);

// rezervări pe status
$statusRes = $conn->query("
    SELECT status, COUNT(*) AS c
    FROM reservations
    GROUP BY status
    ORDER BY status
");
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <title>Admin</title>
</head>
<body>

<h2>Admin dashboard</h2>
<p>Salut, <b><?php echo htmlspecialchars($_SESSION["username"]); ?></b>!</p>
<p><a href="logout.php">Logout</a></p>

<hr>

<ul>
  <li><a href="rooms_list.php">Gestionare camere</a></li>
  <li><a href="reservations_list.php">Gestionare rezervări</a></li>
  <li><a href="messages_list.php">Mesaje contact</a></li>
</ul>

<hr>

<h3>Statistici</h3>

<ul>
  <li>Total camere: <b><?php echo $totalRooms; ?></b></li>
  <li>Total rezervări: <b><?php echo $totalReservations; ?></b></li>
  <li>Venit total (confirmate / check-in / check-out): 
      <b><?php echo number_format($totalRevenue, 2); ?></b>
  </li>
</ul>

<h4>Rezervări pe status</h4>

<table border="1" cellpadding="6" cellspacing="0">
  <tr>
    <th>Status</th>
    <th>Număr rezervări</th>
  </tr>

  <?php if ($statusRes && $statusRes->num_rows > 0): ?>
    <?php while ($s = $statusRes->fetch_assoc()): ?>
      <tr>
        <td><?php echo htmlspecialchars($s["status"]); ?></td>
        <td><?php echo (int)$s["c"]; ?></td>
      </tr>
    <?php endwhile; ?>
  <?php else: ?>
    <tr><td colspan="2">Nu există rezervări.</td></tr>
  <?php endif; ?>
</table>

</body>
</html>
