<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function csrf_token(): string {
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }
    return $_SESSION["csrf_token"];
}

function csrf_field(): string {
    $t = htmlspecialchars(csrf_token());
    return '<input type="hidden" name="csrf_token" value="'.$t.'">';
}

function csrf_verify(): void {
    $token = $_POST["csrf_token"] ?? "";
    if (!$token || empty($_SESSION["csrf_token"]) || !hash_equals($_SESSION["csrf_token"], $token)) {
        http_response_code(403);
        die("CSRF invalid");
    }
}

/**
 * Anti-bot simplu (rate-limit pe sesiune) - nu afectează UX.
 */
function rate_limit(string $key, int $seconds): void {
    $now = time();
    $k = "rl_" . $key;
    if (!empty($_SESSION[$k]) && ($now - (int)$_SESSION[$k]) < $seconds) {
        http_response_code(429);
        die("Prea multe cereri. Încearcă din nou în câteva secunde.");
    }
    $_SESSION[$k] = $now;
}
