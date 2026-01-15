<?php
require_once "includes/auth.php";
require_login();
require_once "includes/db.php";

$role = $_SESSION["role"];
$userId = (int)$_SESSION["user_id"];

$errors = [];

// lista camere pentru select
$roomsRes = $conn->query("SELECT id, room_number, price FROM rooms ORDER BY room_number");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $room_id = (int)($_POST["room_id"] ?? 0);
    $start_date = $_POST["start_date"] ?? "";
    $end_date   = $_POST["end_date"] ?? "";
    $status     = $_POST["status"] ?? "pending";

    // pentru admin/recepționer putem alege user
    $target_user_id = $userId;
    if ($role === "admin" || $role === "receptioner") {
        $target_user_id = (int)($_POST["user_id"] ?? $userId);
    }

    if ($room_id <= 0) $errors[] = "Selectează o cameră.";
    if ($start_date === "" || $end_date === "") $errors[] = "Selectează perioada.";
    if (!in_array($status, ["pending","confirmed","checked_in","checked_out","cancelled"], true)) $errors[] = "Status invalid.";

    // validare perioadă + calcul zile
    $d1 = strtotime($start_date);
    $d2 = strtotime($end_date);
    if ($d1 === false || $d2 === false || $d2 <= $d1) {
        $errors[] = "Data de final trebuie să fie după data de început.";
    }

    // luăm prețul camerei
    $price = 0;
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT price FROM rooms WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $room_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stmt->close();

        if (!$row) {
            $errors[] = "Camera nu există.";
        } else {
            $price = (float)$row["price"];
        }
    }

    // verificăm suprapunere (camera să nu fie deja rezervată) - simplu
    if (empty($errors)) {
        $stmt = $conn->prepare(
            "SELECT id FROM reservations
             WHERE room_id = ?
               AND status IN ('pending','confirmed','checked_in')
               AND NOT (end_date <= ? OR start_date >= ?)
             LIMIT 1"
        );
        $stmt->bind_param("iss", $room_id, $start_date, $end_date);
        $stmt->execute();
        $conflict = $stmt->get_result();
        $stmt->close();

        if ($conflict && $conflict->num_rows > 0) {
            $errors[] = "Camera este deja rezervată în perioada aleasă.";
        }
    }

    if (empty($errors)) {
        $days = (int)ceil(($d2 - $d1) / 86400);
        $total_price = $days * $price;

        $stmt = $conn->prepare("INSERT INTO reservations (user_id, room_id, start_date, end_date, status, total_price)
                                VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisssd", $target_user_id, $room_id, $start_date, $end_date, $status, $total_price);

        if ($stmt->execute()) {
            header("Location: reservations_list.php");
            exit;
        } else {
            $errors[] = "Eroare INSERT: " . $conn->error;
        }
        $stmt->close();
    }
}

// lista useri (doar admin/recepționer)
$usersRes = null;
if ($role === "admin" || $role === "receptioner") {
    $usersRes = $conn->query("SELECT id, username FROM users ORDER BY username");
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Rezervare nouă</title>
</head>
<body>

<h2>Rezervare nouă</h2>
<p><a href="reservations_list.php">Înapoi</a></p>

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
    <?php if ($usersRes): ?>
        <p>
            <label>User:</label><br>
            <select name="user_id">
                <?php while ($u = $usersRes->fetch_assoc()): ?>
                    <option value="<?php echo (int)$u["id"]; ?>">
                        <?php echo htmlspecialchars($u["username"]); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </p>
    <?php endif; ?>

    <p>
        <label>Cameră:</label><br>
        <select name="room_id">
            <?php if ($roomsRes && $roomsRes->num_rows > 0): ?>
                <?php while ($ro = $roomsRes->fetch_assoc()): ?>
                    <option value="<?php echo (int)$ro["id"]; ?>">
                        <?php echo htmlspecialchars($ro["room_number"]); ?> (<?php echo htmlspecialchars($ro["price"]); ?>)
                    </option>
                <?php endwhile; ?>
            <?php endif; ?>
        </select>
    </p>

    <p>
        <label>Start date:</label><br>
        <input type="date" name="start_date" value="<?php echo htmlspecialchars($_POST["start_date"] ?? ""); ?>">
    </p>

    <p>
        <label>End date:</label><br>
        <input type="date" name="end_date" value="<?php echo htmlspecialchars($_POST["end_date"] ?? ""); ?>">
    </p>

    <p>
        <label>Status:</label><br>
        <select name="status">
            <option value="pending">pending</option>
            <option value="confirmed">confirmed</option>
            <option value="checked_in">checked_in</option>
            <option value="checked_out">checked_out</option>
            <option value="cancelled">cancelled</option>
        </select>
    </p>

    <button type="submit">Salvează</button>
</form>

</body>
</html>
