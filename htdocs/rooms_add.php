<?php
require_once "includes/auth.php";
require_role("admin");

require_once "includes/db.php";
require_once "includes/security.php";

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_verify();
    $room_number = trim($_POST["room_number"] ?? "");
    $type        = $_POST["type"] ?? "single";
    $price       = trim($_POST["price"] ?? "");
    $capacity    = (int)($_POST["capacity"] ?? 1);
    $status      = $_POST["status"] ?? "available";

    // validări simple
    if ($room_number === "") $errors[] = "Numărul camerei este obligatoriu.";
    if (!in_array($type, ["single","double","suite"], true)) $errors[] = "Tip invalid.";
    if ($price === "" || !is_numeric($price) || (float)$price <= 0) $errors[] = "Preț invalid.";
    if ($capacity <= 0) $errors[] = "Capacitate invalidă.";
    if (!in_array($status, ["available","occupied","maintenance"], true)) $errors[] = "Status invalid.";

    if (empty($errors)) {
        $sql = "INSERT INTO rooms (room_number, type, price, capacity, status) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdis", $room_number, $type, $price, $capacity, $status);

        if ($stmt->execute()) {
            header("Location: rooms_list.php");
            exit;
        } else {
            $errors[] = "Eroare INSERT: " . $conn->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Adaugă cameră</title>
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

<h2>Adaugă cameră</h2>
<p><a href="rooms_list.php">Înapoi</a></p>

<?php if (!empty($errors)): ?>
    <div style="border:1px solid red; padding:10px;">
        <ul>
            <?php foreach ($errors as $e): ?>
                <li><?php echo htmlspecialchars($e); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post">
    <?php echo csrf_field(); ?>
    <p>
        <label>Număr cameră:</label><br>
        <input type="text" name="room_number" value="<?php echo htmlspecialchars($_POST["room_number"] ?? ""); ?>">
    </p>

    <p>
        <label>Tip:</label><br>
        <select name="type">
            <option value="single">single</option>
            <option value="double">double</option>
            <option value="suite">suite</option>
        </select>
    </p>

    <p>
        <label>Preț:</label><br>
        <input type="text" name="price" value="<?php echo htmlspecialchars($_POST["price"] ?? ""); ?>">
    </p>

    <p>
        <label>Capacitate:</label><br>
        <input type="number" name="capacity" value="<?php echo htmlspecialchars($_POST["capacity"] ?? "1"); ?>">
    </p>

    <p>
        <label>Status:</label><br>
        <select name="status">
            <option value="available">available</option>
            <option value="occupied">occupied</option>
            <option value="maintenance">maintenance</option>
        </select>
    </p>

    <button type="submit">Salvează</button>
</form>
    </div>
</body>
</html>
