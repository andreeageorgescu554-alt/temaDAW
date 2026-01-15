<?php
require_once "includes/auth.php";
require_role("client");
require_once "includes/db.php";

$userId = (int)$_SESSION["user_id"];

// total rezervări
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM reservations WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$total = (int)($row["total"] ?? 0);
$stmt->close();

// rezervări active
$stmt = $conn->prepare(
    "SELECT COUNT(*) AS active
     FROM reservations
     WHERE user_id = ?
       AND status IN ('pending','confirmed','checked_in')"
);
$stmt->bind_param("i", $userId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$active = (int)($row["active"] ?? 0);
$stmt->close();

// ultimele 5 rezervări
$stmt = $conn->prepare(
    "SELECT r.id, ro.room_number, r.start_date, r.end_date, r.status, r.total_price
     FROM reservations r
     JOIN rooms ro ON ro.id = r.room_id
     WHERE r.user_id = ?
     ORDER BY r.created_at DESC
     LIMIT 5"
);
$stmt->bind_param("i", $userId);
$stmt->execute();
$lastRes = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Client</title>
</head>
<body>

<h2>Dashboard Client</h2>
<p>Salut, <b><?php echo htmlspecialchars($_SESSION["username"]); ?></b>!</p>

<hr>

<p>Total rezervări: <b><?php echo $total; ?></b></p>
<p>Rezervări active: <b><?php echo $active; ?></b></p>

<p>
  <a href="reservations_list.php">Rezervările mele</a> |
  <a href="reservations_add.php">Rezervare nouă</a> |
  <a href="logout.php">Logout</a>
</p>

<h3>Ultimele 5 rezervări</h3>
<table border="1" cellpadding="6" cellspacing="0">
  <tr>
    <th>Cameră</th>
    <th>Start</th>
    <th>End</th>
    <th>Status</th>
    <th>Total</th>
    <th>Acțiuni</th>
  </tr>

  <?php if ($lastRes && $lastRes->num_rows > 0): ?>
    <?php while ($r = $lastRes->fetch_assoc()): ?>
      <tr>
        <td><?php echo htmlspecialchars($r["room_number"]); ?></td>
        <td><?php echo htmlspecialchars($r["start_date"]); ?></td>
        <td><?php echo htmlspecialchars($r["end_date"]); ?></td>
        <td><?php echo htmlspecialchars($r["status"]); ?></td>
        <td><?php echo htmlspecialchars($r["total_price"]); ?></td>
        <td>
          <a href="reservations_edit.php?id=<?php echo (int)$r["id"]; ?>">Edit</a> |
          <a href="reservations_delete.php?id=<?php echo (int)$r["id"]; ?>"
             onclick="return confirm('Sigur ștergi rezervarea?');">Delete</a>
        </td>
      </tr>
    <?php endwhile; ?>
  <?php else: ?>
    <tr><td colspan="6">Nu ai rezervări încă.</td></tr>
  <?php endif; ?>
</table>

</body>
</html>
