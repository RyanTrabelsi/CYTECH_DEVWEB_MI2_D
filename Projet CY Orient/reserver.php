<?php
session_start();
require_once 'db_connect.php';

// Process search if form submitted
$search_results = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $search_term = '%' . $_POST['depart'] . '%'; // Using the "À" field as search
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM trips WHERE destination LIKE ? OR country LIKE ?");
        $stmt->execute([$search_term, $search_term]);
        $search_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Search error: " . $e->getMessage());
    }
}

// Always get random trips for initial display
try {
    $stmt = $pdo->query("SELECT * FROM trips ORDER BY RAND() LIMIT 6");
    $random_trips = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $random_trips = [];
    error_log("Database error: " . $e->getMessage());
}

// Get trip details if ID is requested (for modal)
$trip_details = null;
if (isset($_GET['trip_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM trips WHERE id = ?");
        $stmt->execute([$_GET['trip_id']]);
        $trip_details = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Trip details error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width">
  <title>CY-Orient - Réserver</title>
  <link href="style.css" rel="stylesheet" type="text/css" />
  <style>
    .destination-item {
        background-size: cover;
        background-position: center;
        cursor: pointer;
    }
    .search-results {
        margin-top: 30px;
    }
    .no-results {
        color: #ff0000;
        text-align: center;
        padding: 20px;
    }
    
    /* Modal Styles (added) */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.7);
    }
    .modal-content {
        background-color: #fefefe;
        margin: 5% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 60%;
        max-width: 700px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    }
    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }
    .close:hover {
        color: black;
    }
    .trip-details {
        display: flex;
        gap: 20px;
    }
    .trip-image {
        flex: 1;
    }
    .trip-image img {
        width: 100%;
        border-radius: 8px;
    }
    .trip-info {
        flex: 2;
    }
    .trip-price {
        font-size: 24px;
        color: #eaaa37;
        font-weight: bold;
        margin: 10px 0;
    }
    .book-btn {
        background-color: #eaaa37;
        color: white;
        border: none;
        padding: 10px 20px;
        font-size: 16px;
        border-radius: 5px;
        cursor: pointer;
        margin-top: 20px;
    }
    .book-btn:hover {
        background-color: #d99a27;
    }
  </style>
</head>

<body>
  <!-- Modal Structure (added) -->
  <div id="tripModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <?php if ($trip_details): ?>
        <div class="trip-details">
            <div class="trip-image">
                <img src="image/trips/<?= htmlspecialchars($trip_details['image_filename']) ?>" alt="<?= htmlspecialchars($trip_details['destination']) ?>">
            </div>
            <div class="trip-info">
                <h2><?= htmlspecialchars($trip_details['destination']) ?>, <?= htmlspecialchars($trip_details['country']) ?></h2>
                <p><?= htmlspecialchars($trip_details['description']) ?></p>
                
                <div class="trip-meta">
                    <?php if (!empty($trip_details['departure_city'])): ?>
                        <p><strong>Départ de:</strong> <?= htmlspecialchars($trip_details['departure_city']) ?></p>
                    <?php endif; ?>
                    
                    <?php if (!empty($trip_details['duration_days'])): ?>
                        <p><strong>Durée:</strong> <?= htmlspecialchars($trip_details['duration_days']) ?> jours</p>
                    <?php endif; ?>
                    
                    <?php if (!empty($trip_details['airline'])): ?>
                        <p><strong>Compagnie aérienne:</strong> <?= htmlspecialchars($trip_details['airline']) ?></p>
                    <?php endif; ?>
                    
                    <?php if (!empty($trip_details['flight_duration'])): ?>
                        <p><strong>Durée du vol:</strong> <?= htmlspecialchars($trip_details['flight_duration']) ?></p>
                    <?php endif; ?>
                    
                    <?php if (!empty($trip_details['included_services'])): ?>
                        <p><strong>Services inclus:</strong> <?= htmlspecialchars($trip_details['included_services']) ?></p>
                    <?php endif; ?>
                </div>
                
                <div class="trip-price">Prix: <?= number_format($trip_details['price'], 0) ?>€</div>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <button class="book-btn" onclick="alert('Fonctionnalité de réservation à venir!')">Réserver maintenant</button>
                <?php else: ?>
                    <button class="book-btn" onclick="window.location.href='login_form.php?redirect=reserver.php?trip_id=<?= $trip_details['id'] ?>'">Se connecter pour réserver</button>
                <?php endif; ?>
            </div>
        </div>
        <?php else: ?>
            <p>Désolé, les détails de ce voyage ne sont pas disponibles.</p>
        <?php endif; ?>
    </div>
  </div>

  <!-- Original content remains exactly the same -->
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
    <video id="background-video" autoplay loop muted>
      <source src="image/back.mp4" type="video/mp4">
    </video> 
    <div class="landing-page">
      <h1 class="big-title">Réservez dés maintenant parmi nos centaines d'offres</h1>
    </div>
    <div class="forms">
    <form method="post">
      <div class="select1">
      <select name="ar" id="ar">
        <option selected="selected">Aller-Retour</option>
        <option>Aller simple</option>
        <option>Multidestinations</option>
      </select>
      </div>
      <div class="form-group">
      <label for="provenance">De</label>
      <input type="text" name="provenance" id="provenance" placeholder="Oujda,Djerba,...">
      <label for="depart">À</label>
      <input type="text" name="depart" id="depart" placeholder="Mascate,Algérie,..." required>
      <label for="aller">Du</label>
      <input type="date" name="aller" id="aller" placeholder="05/02/2025">
      <label for="retour">Au</label>
      <input type="date" name="retour" id="retour" placeholder="10/02/2025">
      <label for="adultes">Adulte(s)</label>
      <select name="adultes" id="adultes">
        <?php for($i=0; $i<=6; $i++): ?>
          <option value="<?= $i ?>" <?= $i===0 ? 'selected' : '' ?>><?= $i ?></option>
        <?php endfor; ?>
      </select>
      <label for="enfants">Enfant(s)</label>
      <select name="enfants" id="enfants">
        <?php for($i=0; $i<=6; $i++): ?>
          <option value="<?= $i ?>" <?= $i===0 ? 'selected' : '' ?>><?= $i ?></option>
        <?php endfor; ?>
      </select>
    </div>
      <div class="form-submit">
      <button type="submit" name="recherche" id="recherche">Lancer la recherche</button>
    </div>
    </form>
    </div>
  </section>

  <!-- Search Results Section (modified to include onclick) -->
  <?php if (!empty($_POST)): ?>
  <section id="search-results" class="search-results">
    <h2>Résultats pour "<?= htmlspecialchars($_POST['depart']) ?>"</h2>
    <div class="destinations-group">
      <?php if (!empty($search_results)): ?>
        <?php foreach ($search_results as $trip): ?>
        <div class="destination-item" 
             style="background-image: url('image/trips/<?= htmlspecialchars($trip['image_filename']) ?>')"
             onclick="window.location.href='reserver.php?trip_id=<?= $trip['id'] ?>'">
            <div class="overlay">
                <span class="prix">Dès <?= number_format($trip['price'], 0) ?>€</span>
                <span class="nom"><?= htmlspecialchars($trip['destination']) ?></span>
                <span class="country"><?= htmlspecialchars($trip['country']) ?></span>
            </div>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="no-results">Aucun voyage trouvé pour "<?= htmlspecialchars($_POST['depart']) ?>"</p>
      <?php endif; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- Random Trips Section (modified to include onclick) -->
  <section id="destinations">
    <h2>Destinations populaires</h2>
    <div class="destinations-group">
      <?php foreach ($random_trips as $trip): ?>
      <div class="destination-item" 
           style="background-image: url('image/trips/<?= htmlspecialchars($trip['image_filename']) ?>')"
           onclick="window.location.href='reserver.php?trip_id=<?= $trip['id'] ?>'">
          <div class="overlay">
              <span class="prix">Dès <?= number_format($trip['price'], 0) ?>€</span>
              <span class="nom"><?= htmlspecialchars($trip['destination']) ?></span>
          </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <footer>
    <p>&copy; 2025 CY Orient. Tous droits réservés.</p>
  </footer>

  <!-- Added JavaScript for modal -->
  <script>
    // Show modal if trip_id is in URL
    <?php if (isset($_GET['trip_id'])): ?>
        document.getElementById('tripModal').style.display = 'block';
    <?php endif; ?>

    // Close modal when clicking X
    document.querySelector('.close').addEventListener('click', function() {
        document.getElementById('tripModal').style.display = 'none';
        // Remove trip_id from URL without reloading
        history.replaceState(null, null, window.location.pathname);
    });

    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target == document.getElementById('tripModal')) {
            document.getElementById('tripModal').style.display = 'none';
            history.replaceState(null, null, window.location.pathname);
        }
    });
  </script>
</body>
</html>