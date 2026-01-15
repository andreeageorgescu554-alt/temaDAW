<?php
require_once "includes/auth.php";
require_role("admin");

require_once "includes/db.php";

$id = (int)($_GET["id"] ?? 0);
if ($id <= 0) die("ID invalid.");

$errors = [];

// luăm camera existentă
$stmt = $conn->prepare("SELECT id, room_number, type, price, capacity, status FROM rooms WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$room = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$room) die("Camera nu există.");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $room_number = trim($_POST["room_number"] ?? "");
    $type        = $_POST["type"] ?? "single";
    $price       = trim($_POST["price"] ?? "");
    $capacity    = (int)($_POST["capacity"] ?? 1);
    $status      = $_POST["status"] ?? "available";

    if ($room_number === "") $errors[] = "Numărul camerei este obligatoriu.";
    if (!in_array($type, ["single","double","suite"], true)) $errors[] = "Tip invalid.";
    if ($price === "" || !is_numeric($price) || (float)$price <= 0) $errors[] = "Preț invalid.";
    if ($capacity <= 0) $errors[] = "Capacitate invalidă.";
    if (!in_array($status, ["available","occupied","maintenance"], true)) $errors[] = "Status invalid.";

    if (empty($errors)) {
        $sql = "UPDATE rooms SET room_number=?, type=?, price=?, capacity=?, status=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdisi", $room_number, $type, $price, $capacity, $status, $id);

        if ($stmt->execute()) {
            header("Location: rooms_list.php");
            exit;
        } else {
            $errors[] = "Eroare UPDATE: " . $conn->error;
        }
        $stmt->close();
    }

    // dacă sunt erori, păstrăm valorile introduse pentru reafișare
    $room["room_number"] = $room_number;
    $room["type"] = $type;
    $room["price"] = $price;
    $room["capacity"] = $capacity;
    $room["status"] = $status;
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Edit cameră</title>
</head>
<body>

<h2>Edit cameră</h2>
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
    <p>
        <label>Număr cameră:</label><br>
        <input type="text" name="room_number" value="<?php echo htmlspecialchars($room["room_number"]); ?>">
    </p>

    <p>
        <label>Tip:</label><br>
        <select name="type">
            <option value="single" <?php echo ($room["type"]==="single")?"selected":""; ?>>single</option>
            <option value="double" <?php echo ($room["type"]==="double")?"selected":""; ?>>double</option>
            <option value="suite"  <?php echo ($room["type"]==="suite") ?"selected":""; ?>>suite</option>
        </select>
    </p>

    <p>
        <label>Preț:</label><br>
        <input type="text" name="price" value="<?php echo htmlspecialchars($room["price"]); ?>">
    </p>

    <p>
        <label>Capacitate:</label><br>
        <input type="number" name="capacity" value="<?php echo htmlspecialchars($room["capacity"]); ?>">
    </p>

    <p>
        <label>Status:</label><br>
        <select name="status">
            <option value="available"   <?php echo ($room["status"]==="available")?"selected":""; ?>>available</option>
            <option value="occupied"    <?php echo ($room["status"]==="occupied")?"selected":""; ?>>occupied</option>
            <option value="maintenance" <?php echo ($room["status"]==="maintenance")?"selected":""; ?>>maintenance</option>
        </select>
    </p>

    <button type="submit">Salvează</button>
</form>

</body>
</html>
