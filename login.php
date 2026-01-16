<?php
session_start();
require_once "includes/db.php";

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $pass     = $_POST["password"] ?? "";

    if ($username === "" || $pass === "") {
        $errors[] = "Completează username și parola.";
    }

    if (empty($errors)) {
        // Căutăm userul după username
        $sql = "SELECT id, username, password_hash, role FROM users WHERE username = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res && $res->num_rows === 1) {
            $user = $res->fetch_assoc();

            // Verificare parolă (hash)
            if (password_verify($pass, $user["password_hash"])) {
                // login OK -> setăm sesiunea
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["role"] = $user["role"];

                // Bună practică: schimbăm id-ul sesiunii după login
                session_regenerate_id(true);

                // Redirect în funcție de rol
                if ($user["role"] === "admin") {
                    header("Location: admin.php");
                    exit;
                } elseif ($user["role"] === "receptioner") {
                    header("Location: receptioner.php");
                    exit;
                } else {
                    header("Location: index.php");
                    exit;
                }
            } else {
                $errors[] = "Username sau parola greșită.";
            }
        } else {
            $errors[] = "Username sau parola greșită.";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Login - Hotel</title>
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

<h2>Autentificare</h2>

<?php if (!empty($errors)): ?>
    <div style="border:1px solid red; padding:10px;">
        <b>Erori:</b>
        <ul>
            <?php foreach ($errors as $e): ?>
                <li><?php echo htmlspecialchars($e); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" action="">
    <p>
        <label>Username:</label><br>
        <input type="text" name="username" value="<?php echo htmlspecialchars($_POST["username"] ?? ""); ?>">
    </p>

    <p>
        <label>Parolă:</label><br>
        <input type="password" name="password">
    </p>

    <button type="submit">Login</button>
</form>

<p>Nu ai cont? <a href="register.php">Înregistrare</a></p>
    </div>
</body>
</html>
