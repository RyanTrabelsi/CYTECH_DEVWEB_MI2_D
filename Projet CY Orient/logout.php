<?php
// Start the session (required before destroying it)
session_start();

// Clear all session variables
$_SESSION = [];

// Destroy the session (removes session file from server)
session_destroy();

// Optional: Delete the session cookie from the browser
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Redirect to login page or homepage
header("Location: login_form.php"); // or accueil.html
exit();
?>