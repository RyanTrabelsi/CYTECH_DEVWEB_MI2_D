<?php
// Start session and database connection
session_start();
require_once 'db_connect.php';

// Fetch 6 random trips from database
try {
    $stmt = $pdo->query("SELECT * FROM trips ORDER BY RAND() LIMIT 6");
    $trips = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Fallback if database fails
    $trips = [];
    error_log("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CY Orient</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Abril+Fatface&display=swap" rel="stylesheet">
    <link href="style.css" rel="stylesheet" type="text/css" />
    <style>
        /* Dynamic background image styling */
        .destination-item2 {
            background-size: cover;
            background-position: center;
        }
    </style>
</head>
<body>
    <section class="top-page">
        <header class="header">
          <img src="image/logo.png">
          <nav class="nav1">
            <li><a href="accueil.php">Accueil</a></li>
            <li><a href="presentation.php">Présentation</a></li>
            <li><a href="reserver.php">Réserver</a></li>
            <li><a href="profil.php">Mon compte</a></li>
          </nav>
          <?php if(!isset($_SESSION['user_id'])): ?>
              <input type="submit" value="S'inscrire" name="inscription" id="inscription" onclick="window.location.href='form.php';">
              <input type="submit" value="Se connecter" name="connexion" id="connexion" onclick="window.location.href='login_form.php';">
          <?php else: ?>
              <input type="submit" value="Se déconnecter" name="logout" id="logout" onclick="window.location.href='logout.php';">
          <?php endif; ?>
        </header>
      </section>
    <div class="container1">
        <div class="landing-page">
            <h1 class="big-title" style="font-family: Comic Sans MS">CY ORIENT</h1>
            <h1 class="big-title">Découvrez les plus beaux pays du monde</h1>
        </div>
        <section id="destinations2">
            <div class="destinations-group2">
                <?php if (!empty($trips)): ?>
                    <?php foreach ($trips as $trip): ?>
                    <div class="destination-item2" 
                         style="background-image: url('image/trips/<?= htmlspecialchars($trip['image_filename']) ?>')">
                        <div class="overlay2">
                            <span class="prix2">Dès <?= number_format($trip['price'], 0) ?>€</span>
                            <span class="nom2"><?= htmlspecialchars($trip['destination']) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Fallback content if no trips found -->
                    <p class="no-trips">Aucun voyage disponible pour le moment.</p>
                <?php endif; ?>
            </div>
        </section>
    </div>
    <footer>
        <p>&copy; 2025 CY Orient. Tous droits réservés.</p>
    </footer>
</body>
</html>