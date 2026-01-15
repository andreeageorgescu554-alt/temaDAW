<?php
session_start();
require_once "includes/db.php";

$errors = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name    = trim($_POST["name"] ?? "");
    $email   = trim($_POST["email"] ?? "");
    $subject = trim($_POST["subject"] ?? "");
    $message = trim($_POST["message"] ?? "");

    if ($name === "") $errors[] = "Numele este obligatoriu.";
    if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalid.";
    if ($subject === "") $errors[] = "Subiectul este obligatoriu.";
    if ($message === "" || strlen($message) < 10) $errors[] = "Mesajul trebuie să aibă minim 10 caractere.";

    if (empty($errors)) {
        // 1) Salvare în DB
        $stmt = $conn->prepare("INSERT INTO messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $subject, $message);

        if ($stmt->execute()) {
            // 2) Trimitere email (best effort)
            $to = "admin@hotel.test"; // schimbă cu emailul tău real dacă vrei
            $headers  = "From: " . $email . "\r\n";
            $headers .= "Reply-To: " . $email . "\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

            $body = "Mesaj nou din formularul de contact:\n\n";
            $body .= "Nume: $name\n";
            $body .= "Email: $email\n";
            $body .= "Subiect: $subject\n\n";
            $body .= "Mesaj:\n$message\n";

            // mail() poate returna false dacă nu e configurat serverul local
            $sent = @mail($to, "[Contact Hotel] " . $subject, $body, $headers);

            if ($sent) {
                $success = "Mesaj trimis și salvat. Mulțumim!";
            } else {
                $success = "Mesaj salvat în baza de date. (Email-ul nu a putut fi trimis pe acest server local.)";
            }
        } else {
            $errors[] = "Eroare la salvare: " . $conn->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Contact - Hotel</title>
</head>
<body>

<h2>Contact</h2>
<p><a href="index.php">Înapoi</a></p>

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

<?php if ($success !== ""): ?>
    <div style="border:1px solid green; padding:10px;">
        <?php echo htmlspecialchars($success); ?>
    </div>
<?php endif; ?>

<form method="post">
    <p>
        <label>Nume:</label><br>
        <input type="text" name="name" value="<?php echo htmlspecialchars($_POST["name"] ?? ""); ?>">
    </p>

    <p>
        <label>Email:</label><br>
        <input type="text" name="email" value="<?php echo htmlspecialchars($_POST["email"] ?? ""); ?>">
    </p>

    <p>
        <label>Subiect:</label><br>
        <input type="text" name="subject" value="<?php echo htmlspecialchars($_POST["subject"] ?? ""); ?>">
    </p>

    <p>
        <label>Mesaj:</label><br>
        <textarea name="message" rows="6" cols="50"><?php echo htmlspecialchars($_POST["message"] ?? ""); ?></textarea>
    </p>

    <button type="submit">Trimite</button>
</form>

</body>
</html>
