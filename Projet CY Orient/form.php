<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CY Orient - Sign up</title>
    <link rel="stylesheet" type="text/css" href="style.css"> 
</head>
<body class="sign">
    <section class="top-page">
        <header class="header">
          <img src="image/logo.png">
          <nav class="nav1">
            <li><a href="accueil.php">Accueil</a></li>
            <li><a href="presentation.php">Présentation</a></li>
            <li><a href="reserver.php">Réserver</a></li>
            <li><a href="profil.php">Mon compte</a></li>
          </nav>
          <input type="submit" value="Se connecter" name="connexion" id="connexion" onclick="window.location.href='login_form.php';">
        </header>
      </section>
    <div class="sign-in">
    <div class="form-container">
        <img src="image/logo2.png" alt="Logo">
        <h2>S'inscrire</h2>
        <form action="signup.php" method="post">
            <div class="form-group1">
                <label for="name">Nom</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group1">
                <label for="surname">Prénom</label>
                <input type="text" id="surname" name="surname" required>
            </div>
            <div class="form-group1">
                <label for="contact_info">Email ou Numéro de téléphone</label>
                <input type="text" id="contact_info" name="contact_info" required>
            </div>
            <div class="form-group1">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group1">
                <button type="submit">S'inscrire</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>