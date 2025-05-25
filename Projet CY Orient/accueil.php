<?php
// Start session and database connection
session_start();
require_once 'db_connect.php';

// Handle add to cart/book now
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_to_cart']) || isset($_POST['book_now'])) {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_url'] = 'accueil.php?trip_id='.$_POST['trip_id'];
            header("Location: login_form.php");
            exit();
        }

        $trip_id = $_POST['trip_id'];
        $adults = $_POST['adults'];
        $children = $_POST['children'];
        $total_price = $_POST['total_price'];
        $user_id = $_SESSION['user_id'];
        $accommodation_type = $_POST['accommodation_type'];
        $includes_breakfast = isset($_POST['includes_breakfast']) ? 1 : 0;
        $includes_lunch = isset($_POST['includes_lunch']) ? 1 : 0;
        $includes_dinner = isset($_POST['includes_dinner']) ? 1 : 0;

        // Store booking data in session for the checkout process
        $_SESSION['pending_booking'] = [
            'trip_id' => $trip_id,
            'adults' => $adults,
            'children' => $children,
            'total_price' => $total_price,
            'accommodation_type' => $accommodation_type,
            'includes_breakfast' => $includes_breakfast,
            'includes_lunch' => $includes_lunch,
            'includes_dinner' => $includes_dinner,
            'action' => isset($_POST['book_now']) ? 'book_now' : 'add_to_cart'
        ];

        // Redirect to checkout page if booking now
        if (isset($_POST['book_now'])) {
            header("Location: checkout.php");
            exit();
        } else {
            // Add to cart directly
            try {
                $stmt = $pdo->prepare("INSERT INTO cart (user_id, trip_id, adults, children, total_price, added_date, accommodation_type, includes_breakfast, includes_lunch, includes_dinner)
                                      VALUES (?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?)");
                $stmt->execute([$user_id, $trip_id, $adults, $children, $total_price, $accommodation_type, $includes_breakfast, $includes_lunch, $includes_dinner]);
                $_SESSION['cart_success'] = "Le voyage a été ajouté à votre panier!";
                header("Location: accueil.php?trip_id=".$trip_id);
                exit();
            } catch (PDOException $e) {
                error_log("Cart error: " . $e->getMessage());
                $_SESSION['booking_error'] = "Une erreur s'est produite. Veuillez réessayer.";
            }
        }
    }
}

// Fetch 6 random trips from database
try {
    $stmt = $pdo->query("SELECT * FROM trips ORDER BY RAND() LIMIT 6");
    $trips = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Fallback if database fails
    $trips = [];
    error_log("Database error: " . $e->getMessage());
}

// Get trip details for modal
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
            cursor: pointer;
        }

        /* Chatbot styles */
        .chatbot-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            font-family: 'Montserrat', sans-serif;
        }

        .chatbot-button {
            background-color: #2c3e50;
            color: white;
            border: none;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            font-size: 24px;
            cursor: pointer;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }

        .chatbot-button:hover {
            background-color: #1a252f;
            transform: scale(1.1);
        }

        .chatbot-window {
            display: none;
            width: 350px;
            height: 500px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.2);
            flex-direction: column;
            overflow: hidden;
        }

        .chatbot-header {
            background-color: #2c3e50;
            color: white;
            padding: 15px;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chatbot-close {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
        }

        .chatbot-messages {
            flex: 1;
            padding: 15px;
            overflow-y: auto;
            background-color: #f9f9f9;
        }

        .message {
            margin-bottom: 15px;
            max-width: 80%;
            padding: 10px 15px;
            border-radius: 18px;
            line-height: 1.4;
            font-size: 14px;
        }

        .bot-message {
            background-color: #e3e3e3;
            color: #333;
            border-top-left-radius: 5px;
            align-self: flex-start;
        }

        .user-message {
            background-color: #2c3e50;
            color: white;
            border-top-right-radius: 5px;
            margin-left: auto;
        }

        .chatbot-input {
            display: flex;
            border-top: 1px solid #ddd;
        }

        .chatbot-input input {
            flex: 1;
            padding: 15px;
            border: none;
            outline: none;
        }

        .chatbot-input button {
            background-color: #2c3e50;
            color: white;
            border: none;
            padding: 0 20px;
            cursor: pointer;
        }

        .chatbot-input button:hover {
            background-color: #1a252f;
        }

        /* Animation for opening the chat */
        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .chatbot-window.open {
            display: flex;
            animation: slideUp 0.3s ease forwards;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.7);
            overflow-y: auto;
        }
        .modal-content {
            background-color: #fefefe;
            margin: 2% auto;
            padding: 25px;
            border: 1px solid #888;
            width: 70%;
            max-width: 800px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        .booking-form {
            margin-top: 20px;
        }
        .form-row {
            display: flex;
            margin-bottom: 15px;
            align-items: center;
        }
        .form-row label {
            width: 150px;
            margin-right: 15px;
        }
        .form-row input, .form-row select {
            flex: 1;
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        .price-summary {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .price-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .total-price {
            font-weight: bold;
            font-size: 1.2em;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            margin-top: 10px;
        }
        .action-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .action-buttons button {
            padding: 12px 25px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        .add-to-cart {
            background: #333;
            color: white;
            border: none;
        }
        .book-now {
            background: #eaaa37;
            color: white;
            border: none;
        }
        .notification {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            text-align: center;
        }
        .success {
            background: #d4edda;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
        }
        .meal-options {
            margin: 15px 0;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 8px;
        }
        .meal-options label {
            margin-right: 15px;
            cursor: pointer;
        }
        .accommodation-options {
            margin: 15px 0;
        }
        /* Add this to your existing CSS */
        .meal-options input[type="checkbox"] {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            width: 18px;
            height: 18px;
            border: 2px solid #eaaa37;
            border-radius: 4px;
            outline: none;
            cursor: pointer;
            vertical-align: middle;
            position: relative;
            margin-right: 5px;
        }

        .meal-options input[type="checkbox"]:checked {
            background-color: #eaaa37;
        }

        .meal-options input[type="checkbox"]:checked::after {
            content: "✓";
            position: absolute;
            color: white;
            font-size: 14px;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
        }

        .meal-options label {
            display: inline-flex;
            align-items: center;
            margin-right: 20px;
            cursor: pointer;
            user-select: none;
        }

        /* Modal Image Styling */
        .trip-image {
            width: 100%;
            max-height: 300px; /* Adjust this value as needed */
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .trip-image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain; /* Ensures the image maintains its aspect ratio */
        }

        /* Modal Content Layout */
        .trip-details {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .trip-info {
            width: 100%;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Modal Structure -->
    <div id="tripModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <?php if ($trip_details): ?>
                <!-- Display notifications -->
                <?php if (isset($_SESSION['booking_success'])): ?>
                    <div class="notification success"><?= $_SESSION['booking_success'] ?></div>
                    <?php unset($_SESSION['booking_success']); ?>
                <?php endif; ?>
                <?php if (isset($_SESSION['cart_success'])): ?>
                    <div class="notification success"><?= $_SESSION['cart_success'] ?></div>
                    <?php unset($_SESSION['cart_success']); ?>
                <?php endif; ?>
                <?php if (isset($_SESSION['booking_error'])): ?>
                    <div class="notification error"><?= $_SESSION['booking_error'] ?></div>
                    <?php unset($_SESSION['booking_error']); ?>
                <?php endif; ?>

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
                        </div>

                        <!-- Booking Form -->
                        <form method="post" class="booking-form">
                            <input type="hidden" name="trip_id" value="<?= $trip_details['id'] ?>">

                            <div class="form-row">
                                <label for="adults">Adultes:</label>
                                <select name="adults" id="adults" onchange="calculatePrice()">
                                    <?php for($i=1; $i<=10; $i++): ?>
                                        <option value="<?= $i ?>"><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="form-row">
                                <label for="children">Enfants:</label>
                                <select name="children" id="children" onchange="calculatePrice()">
                                    <?php for($i=0; $i<=10; $i++): ?>
                                        <option value="<?= $i ?>"><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="accommodation-options">
                                <h3>Type d'hébergement:</h3>
                                <select name="accommodation_type" id="accommodation_type" class="form-control">
                                    <option value="hotel">Hôtel</option>
                                    <option value="apartment">Appartement</option>
                                    <option value="villa">Villa</option>
                                    <option value="resort">Résidence de vacances</option>
                                    <option value="hostel">Auberge de jeunesse</option>
                                    <option value="guesthouse">Maison d'hôtes</option>
                                    <option value="bungalow">Bungalow</option>
                                </select>
                            </div>

                            <div class="meal-options">
                                <h3>Options de repas:</h3>
                                <label>
                                    <input type="checkbox" name="includes_breakfast" value="1" checked> Petit-déjeuner
                                </label>
                                <label>
                                    <input type="checkbox" name="includes_lunch" value="1"> Déjeuner
                                </label>
                                <label>
                                    <input type="checkbox" name="includes_dinner" value="1"> Dîner
                                </label>
                            </div>

                            <div class="price-summary">
                                <div class="price-row">
                                    <span>Prix par adulte:</span>
                                    <span id="adult-price"><?= number_format($trip_details['price'], 0) ?>€</span>
                                </div>
                                <div class="price-row">
                                    <span>Prix par enfant:</span>
                                    <span id="child-price"><?= number_format($trip_details['price'] * 0.7, 0) ?>€</span>
                                </div>
                                <div class="price-row total-price">
                                    <span>Total:</span>
                                    <span id="total-price"><?= number_format($trip_details['price'], 0) ?>€</span>
                                </div>
                                <input type="hidden" name="total_price" id="total-price-value" value="<?= $trip_details['price'] ?>">
                            </div>

                            <div class="action-buttons">
                                <button type="submit" name="add_to_cart" class="add-to-cart">Ajouter au panier</button>
                                <button type="submit" name="book_now" class="book-now">Réserver maintenant</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <p>Désolé, les détails de ce voyage ne sont pas disponibles.</p>
            <?php endif; ?>
        </div>
    </div>

    <section class="top-page">
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
                         style="background-image: url('image/trips/<?= htmlspecialchars($trip['image_filename']) ?>')"
                         onclick="window.location.href='accueil.php?trip_id=<?= $trip['id'] ?>'">
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

    <!-- Chatbot Interface -->
    <div class="chatbot-container">
        <div id="chatbotWindow" class="chatbot-window">
            <div class="chatbot-header">
                <span>Assistant CY Orient</span>
                <button class="chatbot-close" onclick="toggleChatbot()">×</button>
            </div>
            <div id="chatbotMessages" class="chatbot-messages">
                <div class="message bot-message">
                    Bonjour ! Je suis l'assistant virtuel de CY Orient. Comment puis-je vous aider aujourd'hui ?
                </div>
            </div>
            <div class="chatbot-input">
                <input type="text" id="userInput" placeholder="Tapez votre message..." onkeypress="handleKeyPress(event)">
                <button onclick="sendMessage()">Envoyer</button>
            </div>
        </div>
        <button class="chatbot-button" onclick="toggleChatbot()">💬</button>
    </div>

    <footer>
        <p>&copy; 2025 CY Orient. Tous droits réservés.</p>
    </footer>

    <script>
        // Toggle chatbot window
        function toggleChatbot() {
            const chatbotWindow = document.getElementById('chatbotWindow');
            chatbotWindow.classList.toggle('open');
        }

        // Send message function
        function sendMessage() {
            const userInput = document.getElementById('userInput');
            const message = userInput.value.trim();

            if (message) {
                addMessage(message, 'user-message');
                userInput.value = '';

                const context = "CY Orient est une plateforme de voyage spécialisée dans les destinations orientales, notamment l'Égypte, la Jordanie, et les Émirats arabes unis.";
                // Send to Flask API
                fetch('http://127.0.0.1:5000/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        question: message,
                        context: context
                    })
                })
                .then(response => response.json())
                .then(data => {
                    const botReply = data.answer || "Désolé, une erreur est survenue.";
                    addMessage(botReply, 'bot-message');
                })
                .catch(error => {
                    console.error('Error:', error);
                    addMessage("Erreur de communication avec le serveur.", 'bot-message');
                });
            }
        }

        // Handle Enter key press
        function handleKeyPress(event) {
            if (event.key === 'Enter') {
                sendMessage();
            }
        }

        // Add message to chat
        function addMessage(text, className) {
            const messagesContainer = document.getElementById('chatbotMessages');
            const messageElement = document.createElement('div');
            messageElement.classList.add('message', className);
            messageElement.textContent = text;
            messagesContainer.appendChild(messageElement);

            // Scroll to bottom
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        // Optional: Auto-open chatbot after 30 seconds if not interacted with
        setTimeout(() => {
            if (!document.getElementById('chatbotWindow').classList.contains('open')) {
                toggleChatbot();
            }
        }, 30000);

        // Show modal if trip_id is in URL
        <?php if (isset($_GET['trip_id'])): ?>
            document.getElementById('tripModal').style.display = 'block';
        <?php endif; ?>

        // Close modal when clicking X
        document.querySelector('.close').addEventListener('click', function() {
            document.getElementById('tripModal').style.display = 'none';
            history.replaceState(null, null, window.location.pathname);
        });

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target == document.getElementById('tripModal')) {
                document.getElementById('tripModal').style.display = 'none';
                history.replaceState(null, null, window.location.pathname);
            }
        });

        // Calculate price based on passengers
        function calculatePrice() {
            const adultPrice = <?= $trip_details['price'] ?? 0 ?>;
            const childPrice = adultPrice * 0.7; // 30% discount for children
            const adults = parseInt(document.getElementById('adults').value);
            const children = parseInt(document.getElementById('children').value);
            const total = (adultPrice * adults) + (childPrice * children);

            document.getElementById('total-price').textContent = total.toFixed(0) + '€';
            document.getElementById('total-price-value').value = total;
        }
    </script>
</body>
</html>
