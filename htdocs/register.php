<?php
session_start();
require_once "includes/db.php";

// Mesaje pentru utilizator
$errors = [];
$success = "";

// Dacă a fost trimis formularul
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // preluare + curățare minimă
    $username = trim($_POST["username"] ?? "");
    $email    = trim($_POST["email"] ?? "");
    $pass1    = $_POST["password"] ?? "";
    $pass2    = $_POST["password2"] ?? "";

    // validări simple (ca la laborator)
    if ($username === "" || strlen($username) < 3) {
        $errors[] = "Username minim 3 caractere.";
    }
    if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email invalid.";
    }
    if (strlen($pass1) < 6) {
        $errors[] = "Parola trebuie să aibă minim 6 caractere.";
    }
    if ($pass1 !== $pass2) {
        $errors[] = "Parolele nu coincid.";
    }

    // dacă nu avem erori -> verificăm dacă user/email există deja
    if (empty($errors)) {
        $sqlCheck = "SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1";
        $stmt = $conn->prepare($sqlCheck);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res && $res->num_rows > 0) {
            $errors[] = "Username sau email există deja.";
        }

        $stmt->close();
    }

    // inserare user
    if (empty($errors)) {
        $hash = password_hash($pass1, PASSWORD_DEFAULT);
        $role = "client";

        $sqlIns = "INSERT INTO users (username, password_hash, role, email) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sqlIns);
        $stmt->bind_param("ssss", $username, $hash, $role, $email);

        if ($stmt->execute()) {
            $success = "Cont creat cu succes! Acum te poți autentifica.";
        } else {
            $errors[] = "Eroare la creare cont: " . $conn->error;
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Înregistrare - Hotel</title>
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

<h2>Înregistrare cont</h2>

<?php if (!empty($errors)): ?>
    <div style="border:1px solid red; padding:10px;">
        <b>Au apărut erori:</b>
        <ul>
            <?php foreach ($errors as $e): ?>
                <li><?php echo htmlspecialchars($e); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if ($success !== ""): ?>
    <div style="border:1px solid green; padding:10px;">
        <?php echo htmlspecialchars($success); ?>
    </div>
<?php endif; ?>

<form method="post" action="">
    <p>
        <label>Username:</label><br>
        <input type="text" name="username" value="<?php echo htmlspecialchars($_POST["username"] ?? ""); ?>">
    </p>

    <p>
        <label>Email:</label><br>
        <input type="text" name="email" value="<?php echo htmlspecialchars($_POST["email"] ?? ""); ?>">
    </p>

    <p>
        <label>Parolă:</label><br>
        <input type="password" name="password">
    </p>

    <p>
        <label>Confirmă parola:</label><br>
        <input type="password" name="password2">
    </p>

    <button type="submit">Creează cont</button>
</form>

<p>Ai deja cont? <a href="login.php">Login</a></p>
    </div>
</body>
</html>
