<?php
require_once "includes/db.php";
require_once "includes/security.php";

$SITE_KEY   = " 6LdJ30wsAAAAAGAmiVA1wXFdzXeHIaXxp5-kZOdO";
$SECRET_KEY = "6LdJ30wsAAAAAIXVQ3pacfRIparaGdHihHzMrdZf";

$errors = [];
$success = "";

function verify_recaptcha($secret, $token) {
    if ($token === "") return false;

    // Varianta 1: file_get_contents (merge pe multe hostinguri)
    $url = "https://www.google.com/recaptcha/api/siteverify";
    $data = http_build_query([
        "secret" => $secret,
        "response" => $token,
        "remoteip" => $_SERVER["REMOTE_ADDR"] ?? ""
    ]);

    $opts = [
        "http" => [
            "method"  => "POST",
            "header"  => "Content-Type: application/x-www-form-urlencoded\r\n",
            "content" => $data,
            "timeout" => 6
        ]
    ];

    $resp = @file_get_contents($url, false, stream_context_create($opts));

    // Varianta 2: fallback cURL (dacă file_get_contents e blocat)
    if ($resp === false && function_exists("curl_init")) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 6,
        ]);
        $resp = curl_exec($ch);
        curl_close($ch);
    }

    if (!$resp) return false;

    $json = json_decode($resp, true);
    return !empty($json["success"]);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    rate_limit("contact", 5);  // anti-spam minim (nu deranjează userul)
    csrf_verify();             // CSRF

    $name    = trim($_POST["name"] ?? "");
    $email   = trim($_POST["email"] ?? "");
    $subject = trim($_POST["subject"] ?? "");
    $message = trim($_POST["message"] ?? "");

    if ($name === "") $errors[] = "Numele este obligatoriu.";
    if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalid.";
    if ($subject === "") $errors[] = "Subiect obligatoriu.";
    if ($message === "" || strlen($message) < 10) $errors[] = "Mesajul trebuie să aibă minim 10 caractere.";

    // reCAPTCHA verify
    $token = $_POST["g-recaptcha-response"] ?? "";
    if (!verify_recaptcha($SECRET_KEY, $token)) {
        $errors[] = "reCAPTCHA invalid. Încearcă din nou.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO messages(name,email,subject,message) VALUES(?,?,?,?)");
        $stmt->bind_param("ssss", $name, $email, $subject, $message);

        if ($stmt->execute()) {
            $success = "Mesaj trimis cu succes (salvat în baza de date).";
            // curățăm câmpurile
            $_POST = [];
        } else {
            $errors[] = "Eroare DB.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <title>Contact</title>
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
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

<h2>Contact</h2>
<p><a href="index.php">Înapoi</a></p>

<?php if (!empty($errors)): ?>
  <div style="color:red;">
    <ul>
      <?php foreach ($errors as $e): ?>
        <li><?php echo htmlspecialchars($e); ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<?php if ($success): ?>
  <div style="color:green;"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<form method="post">
  <?php echo csrf_field(); ?>

  Nume: <input name="name" value="<?php echo htmlspecialchars($_POST["name"] ?? ""); ?>"><br>
  Email: <input name="email" value="<?php echo htmlspecialchars($_POST["email"] ?? ""); ?>"><br>
  Subiect: <input name="subject" value="<?php echo htmlspecialchars($_POST["subject"] ?? ""); ?>"><br>
  Mesaj:<br>
  <textarea name="message" rows="6" cols="50"><?php echo htmlspecialchars($_POST["message"] ?? ""); ?></textarea><br><br>

  <div class="g-recaptcha" data-sitekey="<?php echo htmlspecialchars($SITE_KEY); ?>"></div><br>

  <button type="submit">Trimite</button>
</form>
    </div>
</body>
</html>
