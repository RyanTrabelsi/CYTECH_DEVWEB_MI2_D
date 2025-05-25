<?php
session_start();
require_once 'db_connect.php';

// Redirect if no pending booking
if (!isset($_SESSION['pending_booking'])) {
    header("Location: reserver.php");
    exit();
}

// Fetch user's saved card details
$card_details = null;
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT card_number, card_expiry, card_cvv, card_name FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $card_details = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Card details fetch error: " . $e->getMessage());
    }
}

// Process payment and save booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_payment'])) {
    // Validate card details
    $errors = [];
    $card_number = str_replace(' ', '', $_POST['card_number']);
    $card_expiry = $_POST['card_expiry'];
    $card_cvv = $_POST['card_cvv'];
    $card_name = $_POST['card_name'];
    $save_card = isset($_POST['save_card']);

    if (!preg_match('/^\d{16}$/', $card_number)) {
        $errors[] = "Numéro de carte invalide";
    }

    if (!preg_match('/^\d{2}\/\d{2}$/', $card_expiry)) {
        $errors[] = "Date d'expiration invalide (MM/AA)";
    }

    if (!preg_match('/^\d{3,4}$/', $card_cvv)) {
        $errors[] = "CVV invalide";
    }

    if (empty($card_name)) {
        $errors[] = "Nom sur la carte requis";
    }

    if (empty($errors)) {
        $user_id = $_SESSION['user_id'];
        $booking_data = $_SESSION['pending_booking'];

        try {
            $pdo->beginTransaction();

            // Save card details if requested
            if ($save_card) {
                $stmt = $pdo->prepare("UPDATE users SET
                    card_number = ?,
                    card_expiry = ?,
                    card_cvv = ?,
                    card_name = ?
                    WHERE id = ?");
                $stmt->execute([
                    $card_number, // Store card number in plain text
                    $card_expiry,
                    password_hash($card_cvv, PASSWORD_DEFAULT), // Hash the CVV
                    $card_name,
                    $user_id
                ]);
            }

            // Process the booking
            if ($booking_data['action'] === 'book_now') {
                $stmt = $pdo->prepare("INSERT INTO bookings (user_id, trip_id, adults, children, total_price, booking_date, status, accommodation_type, includes_breakfast, includes_lunch, includes_dinner)
                                      VALUES (?, ?, ?, ?, ?, NOW(), 'confirmed', ?, ?, ?, ?)");
                $stmt->execute([
                    $user_id,
                    $booking_data['trip_id'],
                    $booking_data['adults'],
                    $booking_data['children'],
                    $booking_data['total_price'],
                    $booking_data['accommodation_type'],
                    $booking_data['includes_breakfast'],
                    $booking_data['includes_lunch'],
                    $booking_data['includes_dinner']
                ]);

                $_SESSION['booking_success'] = "Votre réservation a été confirmée!";
            } else {
                $stmt = $pdo->prepare("INSERT INTO cart (user_id, trip_id, adults, children, total_price, added_date, accommodation_type, includes_breakfast, includes_lunch, includes_dinner)
                                      VALUES (?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?)");
                $stmt->execute([
                    $user_id,
                    $booking_data['trip_id'],
                    $booking_data['adults'],
                    $booking_data['children'],
                    $booking_data['total_price'],
                    $booking_data['accommodation_type'],
                    $booking_data['includes_breakfast'],
                    $booking_data['includes_lunch'],
                    $booking_data['includes_dinner']
                ]);

                $_SESSION['cart_success'] = "Le voyage a été ajouté à votre panier!";
            }

            $pdo->commit();

            // Redirect back to booking page
            unset($_SESSION['pending_booking']);
            header("Location: reserver.php?trip_id=".$booking_data['trip_id']);
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Payment processing error: " . $e->getMessage());
            $_SESSION['payment_error'] = "Une erreur s'est produite lors du traitement du paiement. Veuillez réessayer.";
        }
    } else {
        $_SESSION['payment_errors'] = $errors;
    }
}

// Get trip details
try {
    $stmt = $pdo->prepare("SELECT * FROM trips WHERE id = ?");
    $stmt->execute([$_SESSION['pending_booking']['trip_id']]);
    $trip_details = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Trip details error: " . $e->getMessage());
    $trip_details = null;
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width">
  <title>CY-Orient - Paiement</title>
  <link href="style.css" rel="stylesheet" type="text/css" />
  <style>
    .checkout-container {
        max-width: 800px;
        margin: 50px auto;
        padding: 30px;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
    }

    .checkout-header {
        text-align: center;
        margin-bottom: 30px;
    }

    .checkout-summary {
        background: #f9f9f9;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 30px;
    }

    .payment-form .form-group {
        margin-bottom: 20px;
    }

    .payment-form label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
    }

    .payment-form input {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 16px;
    }

    .card-icons {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }

    .card-icons img {
        height: 30px;
    }

    .row {
        display: flex;
        gap: 20px;
    }

    .row .form-group {
        flex: 1;
    }

    .save-card {
        margin: 20px 0;
        display: flex;
        align-items: center;
    }

    .save-card input[type="checkbox"] {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        width: 20px;
        height: 20px;
        border: 2px solid #eaaa37;
        border-radius: 4px;
        outline: none;
        cursor: pointer;
        vertical-align: middle;
        position: relative;
        margin-right: 10px;
    }

    .save-card input[type="checkbox"]:checked {
        background-color: #eaaa37;
    }

    .save-card input[type="checkbox"]:checked::after {
        content: "✓";
        position: absolute;
        color: white;
        font-size: 14px;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
    }

    .save-card label {
        display: inline-flex;
        align-items: center;
        cursor: pointer;
        user-select: none;
    }

    .submit-btn {
        background: #eaaa37;
        color: white;
        border: none;
        padding: 15px 30px;
        font-size: 18px;
        border-radius: 5px;
        cursor: pointer;
        width: 100%;
    }

    .error-message {
        color: #dc3545;
        margin-top: 5px;
    }

    .notification {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 4px;
        text-align: center;
    }

    .error {
        background: #f8d7da;
        color: #721c24;
    }
  </style>
</head>
<body>
  <!-- Header from reserver.php -->
  <header class="header">
    <img src="image/logo.png" alt="Logo">
    <nav class="nav1">
        <li><a href="accueil.php">Accueil</a></li>
        <li><a href="presentation.php">Présentation</a></li>
        <li><a href="reserver.php">Réserver</a></li>
        <li><a href="profil.php">Mon compte</a></li>
    </nav>
    <?php if(isset($_SESSION['user_id'])): ?>
        <?php if($_SESSION['contact_type'] === 'admin'): ?>
            <input type="button" class="admin-button" value="Liste des utilisateurs" onclick="window.location.href='userlist.php';">
        <?php endif; ?>
        <input type="submit" value="Se déconnecter" name="logout" id="logout" onclick="window.location.href='logout.php';">
    <?php else: ?>
        <input type="submit" value="S'inscrire" name="inscription" id="inscription" onclick="window.location.href='form.php';">
        <input type="submit" value="Se connecter" name="connexion" id="connexion" onclick="window.location.href='login_form.php';">
    <?php endif; ?>
  </header>
  <video id="background-video" autoplay loop muted>
      <source src="image/back.mp4" type="video/mp4">
    </video>
  <div class="checkout-container">
    <div class="checkout-header">
      <h1>Paiement sécurisé</h1>
      <p>Veuillez entrer vos informations de paiement</p>
    </div>

    <?php if (isset($_SESSION['payment_errors'])): ?>
      <div class="notification error">
        <?php foreach ($_SESSION['payment_errors'] as $error): ?>
          <p><?= $error ?></p>
        <?php endforeach; ?>
        <?php unset($_SESSION['payment_errors']); ?>
      </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['payment_error'])): ?>
      <div class="notification error">
        <p><?= $_SESSION['payment_error'] ?></p>
        <?php unset($_SESSION['payment_error']); ?>
      </div>
    <?php endif; ?>

    <div class="checkout-summary">
      <h3>Résumé de votre réservation</h3>
      <p><strong>Destination:</strong> <?= htmlspecialchars($trip_details['destination']) ?>, <?= htmlspecialchars($trip_details['country']) ?></p>
      <p><strong>Adultes:</strong> <?= $_SESSION['pending_booking']['adults'] ?></p>
      <p><strong>Enfants:</strong> <?= $_SESSION['pending_booking']['children'] ?></p>
      <p><strong>Total:</strong> <?= number_format($_SESSION['pending_booking']['total_price'], 2) ?>€</p>
    </div>

    <form method="post" class="payment-form">
      <div class="card-icons">
        <img src="image/visa.jpg" alt="Visa">
        <img src="image/mastercard.jpg" alt="Mastercard">
        <img src="image/amex.jpg" alt="American Express">
      </div>

      <div class="form-group">
        <label for="card_number">Numéro de carte</label>
        <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" value="<?= $card_details ? $card_details['card_number'] : '' ?>" required>
      </div>

      <div class="row">
        <div class="form-group">
          <label for="card_expiry">Date d'expiration (MM/AA)</label>
          <input type="text" id="card_expiry" name="card_expiry" placeholder="MM/AA" value="<?= $card_details ? $card_details['card_expiry'] : '' ?>" required>
        </div>

        <div class="form-group">
          <label for="card_cvv">CVV</label>
          <input type="text" id="card_cvv" name="card_cvv" placeholder="123" required>
        </div>
      </div>

      <div class="form-group">
        <label for="card_name">Nom sur la carte</label>
        <input type="text" id="card_name" name="card_name" placeholder="Nom comme affiché sur la carte" value="<?= $card_details ? $card_details['card_name'] : '' ?>" required>
      </div>

      <div class="save-card">
        <input type="checkbox" id="save_card" name="save_card">
        <label for="save_card">Enregistrer ces informations pour mes prochains paiements</label>
      </div>

      <button type="submit" name="process_payment" class="submit-btn">Confirmer le paiement</button>
    </form>
  </div>

  <!-- Footer from reserver.php -->
  <footer>
    <p>&copy; 2025 CY Orient. Tous droits réservés.</p>
  </footer>

  <script>
    // Format card number
    document.getElementById('card_number').addEventListener('input', function(e) {
      let value = e.target.value.replace(/\s+/g, '');
      if (value.length > 0) {
        value = value.match(new RegExp('.{1,4}', 'g')).join(' ');
      }
      e.target.value = value;
    });

    // Format expiry date
    document.getElementById('card_expiry').addEventListener('input', function(e) {
      let value = e.target.value.replace(/\D/g, '');
      if (value.length > 2) {
        value = value.substring(0, 2) + '/' + value.substring(2, 4);
      }
      e.target.value = value;
    });
  </script>
</body>
</html>
