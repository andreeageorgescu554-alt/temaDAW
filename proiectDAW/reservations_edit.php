<?php
require_once __DIR__ . "/includes/auth.php";
require_login();
require_once __DIR__ . "/includes/db.php";

$role = $_SESSION["role"];
$userId = (int)$_SESSION["user_id"];

$id = (int)($_GET["id"] ?? 0);
if ($id <= 0) { die("ID invalid"); }

// Ia rezervarea
$stmt = $conn->prepare("SELECT * FROM reservations WHERE id=?");
$stmt->bind_param("i", $id);
<?php
require_once "includes/auth.php";
require_login();
require_once "includes/db.php";

$role = $_SESSION["role"];
$userId = (int)$_SESSION["user_id"];

$id = (int)($_GET["id"] ?? 0);
if ($id <= 0) die("ID invalid.");

$errors = [];

// luăm rezervarea
$stmt = $conn->prepare(
    "SELECT r.*, ro.price, ro.room_number
     FROM reservations r
     JOIN rooms ro ON ro.id = r.room_id
     WHERE r.id = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$rez = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$rez) die("Rezervarea nu există.");

// clientul vede doar rezervările lui
if ($role === "client" && (int)$rez["user_id"] !== $userId) {
    die("Access denied.");
}

// lista camere
$roomsRes = $conn->query("SELECT id, room_number, price FROM rooms ORDER BY room_number");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $room_id = (int)($_POST["room_id"] ?? 0);
    $start_date = $_POST["start_date"] ?? "";
    $end_date   = $_POST["end_date"] ?? "";
    $status     = $_POST["status"] ?? $rez["status"];

    if ($role === "client") {
        // clientul nu ar trebui să schimbe status în mod real; îl lăsăm ca în DB
        $status = $rez["status"];
    } else {
        if (!in_array($status, ["pending","confirmed","checked_in","checked_out","cancelled"], true)) {
            $errors[] = "Status invalid.";
        }
    }

    if ($room_id <= 0) $errors[] = "Selectează o cameră.";
    if ($start_date === "" || $end_date === "") $errors[] = "Selectează perioada.";

    $d1 = strtotime($start_date);
    $d2 = strtotime($end_date);
    if ($d1 === false || $d2 === false || $d2 <= $d1) {
        $errors[] = "Data de final trebuie să fie după data de început.";
    }

    // luăm preț camera
    $price = 0;
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT price FROM rooms WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $room_id);
        $stmt->execute();
        $r = $stmt->get_result();
        $row = $r ? $r->fetch_assoc() : null;
        $stmt->close();
        if (!$row) $errors[] = "Camera nu există.";
        else $price = (float)$row["price"];
    }

    // conflict check (excludem rezervarea curentă)
    if (empty($errors)) {
        $stmt = $conn->prepare(
            "SELECT id FROM reservations
             WHERE room_id = ?
               AND id <> ?
               AND status IN ('pending','confirmed','checked_in')
               AND NOT (end_date <= ? OR start_date >= ?)
             LIMIT 1"
        );
        $stmt->bind_param("iiss", $room_id, $id, $start_date, $end_date);
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

        $stmt = $conn->prepare("UPDATE reservations
                                SET room_id=?, start_date=?, end_date=?, status=?, total_price=?
                                WHERE id=?");
        $stmt->bind_param("isssdi", $room_id, $start_date, $end_date, $status, $total_price, $id);

        if ($stmt->execute()) {
            header("Location: reservations_list.php");
            exit;
        } else {
            $errors[] = "Eroare UPDATE: " . $conn->error;
        }
        $stmt->close();
    }

    // reafișare valori
    $rez["room_id"] = $room_id;
    $rez["start_date"] = $start_date;
    $rez["end_date"] = $end_date;
    $rez["status"] = $status;
    $rez["total_price"] = $total_price ?? $rez["total_price"];
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Edit rezervare</title>
</head>
<body>

<h2>Edit rezervare</h2>
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
    <p>
        <label>Cameră:</label><br>
        <select name="room_id">
            <?php while ($ro = $roomsRes->fetch_assoc()): ?>
                <option value="<?php echo (int)$ro["id"]; ?>"
                    <?php echo ((int)$rez["room_id"] === (int)$ro["id"]) ? "selected" : ""; ?>>
                    <?php echo htmlspecialchars($ro["room_number"]); ?> (<?php echo htmlspecialchars($ro["price"]); ?>)
                </option>
            <?php endwhile; ?>
        </select>
    </p>

    <p>
        <label>Start:</label><br>
        <input type="date" name="start_date" value="<?php echo htmlspecialchars($rez["start_date"]); ?>">
    </p>

    <p>
        <label>End:</label><br>
        <input type="date" name="end_date" value="<?php echo htmlspecialchars($rez["end_date"]); ?>">
    </p>

    <p>
        <label>Status:</label><br>
        <?php if ($role === "client"): ?>
            <input type="text" value="<?php echo htmlspecialchars($rez["status"]); ?>" disabled>
            <small>(clientul nu schimbă status)</small>
        <?php else: ?>
            <select name="status">
                <?php
                $opts = ["pending","confirmed","checked_in","checked_out","cancelled"];
                foreach ($opts as $opt):
                ?>
                    <option value="<?php echo $opt; ?>" <?php echo ($rez["status"]===$opt)?"selected":""; ?>>
                        <?php echo $opt; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>
    </p>

    <button type="submit">Salvează</button>
</form>

<p>Total curent: <b><?php echo htmlspecialchars($rez["total_price"]); ?></b></p>

</body>
</html>
