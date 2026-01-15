<?php
require_once "includes/auth.php";
require_login();
require_once "includes/db.php";

$role = $_SESSION["role"];
$userId = (int)$_SESSION["user_id"];

// --- filtre din GET ---
$status = trim($_GET["status"] ?? "");     // ex: pending
$roomId  = (int)($_GET["room_id"] ?? 0);   // ex: 3
$q       = trim($_GET["q"] ?? "");         // username search (admin/receptioner)

// whitelist status (ca sa nu pui orice)
$allowedStatus = ["pending","confirmed","checked_in","checked_out","cancelled"];
if ($status !== "" && !in_array($status, $allowedStatus, true)) {
    $status = "";
}

// lista camere pentru dropdown
$roomsList = $conn->query("SELECT id, room_number FROM rooms ORDER BY room_number");

// --- construim query dinamic ---
if ($role === "admin" || $role === "receptioner") {

    $sql = "SELECT r.id, u.username, ro.room_number, r.start_date, r.end_date, r.status, r.total_price
            FROM reservations r
            JOIN users u ON u.id = r.user_id
            JOIN rooms ro ON ro.id = r.room_id
            WHERE 1=1";

    $types = "";
    $params = [];

    if ($status !== "") {
        $sql .= " AND r.status = ?";
        $types .= "s";
        $params[] = $status;
    }

    if ($roomId > 0) {
        $sql .= " AND r.room_id = ?";
        $types .= "i";
        $params[] = $roomId;
    }

    if ($q !== "") {
        $sql .= " AND u.username LIKE ?";
        $types .= "s";
        $params[] = "%" . $q . "%";
    }

    $sql .= " ORDER BY r.created_at DESC";
    $stmt = $conn->prepare($sql);

    if ($types !== "") {
        $stmt->bind_param($types, ...$params);
    }

} else {

    $sql = "SELECT r.id, u.username, ro.room_number, r.start_date, r.end_date, r.status, r.total_price
            FROM reservations r
            JOIN users u ON u.id = r.user_id
            JOIN rooms ro ON ro.id = r.room_id
            WHERE r.user_id = ?";

    $types = "i";
    $params = [$userId];

    if ($status !== "") {
        $sql .= " AND r.status = ?";
        $types .= "s";
        $params[] = $status;
    }

    if ($roomId > 0) {
        $sql .= " AND r.room_id = ?";
        $types .= "i";
        $params[] = $roomId;
    }

    $sql .= " ORDER BY r.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$res = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <title>Rezervări</title>
</head>
<body>

<h2>Rezervări</h2>
<p><a href="index.php">Înapoi</a> | <a href="logout.php">Logout</a></p>

<p><a href="reservations_add.php">+ Rezervare nouă</a></p>

<hr>

<h3>Filtrare / Căutare</h3>
<form method="get" action="reservations_list.php">
  Status:
  <select name="status">
    <option value="">(toate)</option>
    <?php foreach ($allowedStatus as $opt): ?>
      <option value="<?php echo $opt; ?>" <?php echo ($status === $opt) ? "selected" : ""; ?>>
        <?php echo $opt; ?>
      </option>
    <?php endforeach; ?>
  </select>

  Cameră:
  <select name="room_id">
    <option value="0">(toate)</option>
    <?php if ($roomsList): ?>
      <?php while ($ro = $roomsList->fetch_assoc()): ?>
        <option value="<?php echo (int)$ro["id"]; ?>" <?php echo ($roomId === (int)$ro["id"]) ? "selected" : ""; ?>>
          <?php echo htmlspecialchars($ro["room_number"]); ?>
        </option>
      <?php endwhile; ?>
    <?php endif; ?>
  </select>

  <?php if ($role === "admin" || $role === "receptioner"): ?>
    User:
    <input name="q" value="<?php echo htmlspecialchars($q); ?>" placeholder="caută username">
  <?php endif; ?>

  <button type="submit">Aplică</button>
  <a href="reservations_list.php">Reset</a>
</form>

<br>

<table border="1" cellpadding="6" cellspacing="0">
  <tr>
    <th>User</th>
    <th>Cameră</th>
    <th>Start</th>
    <th>End</th>
    <th>Status</th>
    <th>Total</th>
    <th>Acțiuni</th>
  </tr>

  <?php if ($res && $res->num_rows > 0): ?>
    <?php while ($r = $res->fetch_assoc()): ?>
      <tr>
        <td><?php echo htmlspecialchars($r["username"]); ?></td>
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
    <tr><td colspan="7">Nu există rezervări pentru filtrele alese.</td></tr>
  <?php endif; ?>
</table>

</body>
</html>
