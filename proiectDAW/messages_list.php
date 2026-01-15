<?php
require_once "includes/auth.php";
require_role("admin");
require_once "includes/db.php";

$res = $conn->query(
    "SELECT id, name, email, subject, message, created_at
     FROM messages
     ORDER BY created_at DESC"
);
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <title>Mesaje Contact</title>
</head>
<body>

<h2>Mesaje primite (Contact)</h2>
<p><a href="admin.php">Înapoi</a> | <a href="logout.php">Logout</a></p>

<table border="1" cellpadding="6" cellspacing="0">
  <tr>
    <th>Data</th>
    <th>Nume</th>
    <th>Email</th>
    <th>Subiect</th>
    <th>Mesaj</th>
  </tr>

  <?php if ($res && $res->num_rows > 0): ?>
    <?php while ($m = $res->fetch_assoc()): ?>
      <tr>
        <td><?php echo htmlspecialchars($m["created_at"]); ?></td>
        <td><?php echo htmlspecialchars($m["name"]); ?></td>
        <td><?php echo htmlspecialchars($m["email"]); ?></td>
        <td><?php echo htmlspecialchars($m["subject"]); ?></td>
        <td><?php echo nl2br(htmlspecialchars($m["message"])); ?></td>
      </tr>
    <?php endwhile; ?>
  <?php else: ?>
    <tr><td colspan="5">Nu există mesaje.</td></tr>
  <?php endif; ?>
</table>

</body>
</html>
