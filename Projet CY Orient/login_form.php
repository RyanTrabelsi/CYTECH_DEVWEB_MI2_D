<?php
// MUST be first - before any output
session_start();

// Debugging - check if session is working
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: accueil.php");
    exit();
}

// Handle error messages
$error_message = "";
if (isset($_SESSION['error'])) {
    $error_message = $_SESSION['error'];
    unset($_SESSION['error']); // Clear after displaying
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CY Orient - Connexion</title>
    <link rel="stylesheet" type="text/css" href="style.css"> 
</head>
<body class="sign">
    <section class="top-page">
        <header class="header">
            <img src="image/logo.png" alt="Logo">
            <nav class="nav1">
                <li><a href="accueil.php">Accueil</a></li>
                <li><a href="presentation.php">Présentation</a></li>
                <li><a href="reserver.php">Réserver</a></li>
                <li><a href="profil.php">Mon compte</a></li>
            </nav>
            <input type="submit" value="S'inscrire" name="inscription" id="inscription" 
                   onclick="window.location.href='form.php';">
        </header>
    </section>
    
    <div class="sign-in">
        <div class="form-container">
            <img src="image/logo2.png" alt="Logo">
            <h2>Connexion</h2>
            
            <?php if (!empty($error_message)): ?>
                <div class="error-message" style="color: red; margin-bottom: 10px;">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <form action="login.php" method="post">
                <div class="form-group1">
                    <label for="contact_info">Email ou Numéro de téléphone</label>
                    <input type="text" id="contact_info" name="contact_info" required>
                </div>
                <div class="form-group1">
                    <label for="password">Mot de Passe</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group1">
                    <button type="submit">Connexion</button>
                </div>
            </form>
            
            <div class="signup">
                <p><a href="form.php">Pas de compte encore? Inscrivez-vous ici!</a></p>
            </div>
        </div>
    </div>
</body>
</html>