<?php
// Start session and database connection
session_start();
require_once 'db_connect.php';

// Verify admin status (you should add proper admin verification)
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: accueil.php');
    exit();
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CY Orient - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Abril+Fatface&display=swap" rel="stylesheet">
    <link href="style.css" rel="stylesheet" type="text/css" />
    <style>
        /* Dynamic background image styling */
        .destination-item2 {
            background-size: cover;
            background-position: center;
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
        
        /* Admin button style */
        .admin-button {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 10px 15px;
            margin-right: 10px;
            border-radius: 5px;
            cursor: pointer;
            font-family: 'Montserrat', sans-serif;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        
        .admin-button:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>
    <section class="top-page">
        <header class="header">
          <img src="image/logo.png">
          <nav class="nav1">
            <li><a href="admin_accueil.php">Accueil</a></li>
            <li><a href="presentation.php">Présentation</a></li>
            <li><a href="reserver.php">Réserver</a></li>
            <li><a href="profil.php">Mon compte</a></li>
          </nav>
          <?php if(isset($_SESSION['user_id'])): ?>
              <input type="button" class="admin-button" value="Liste des utilisateurs" onclick="window.location.href='userlist.php';">
              <input type="submit" value="Se déconnecter" name="logout" id="logout" onclick="window.location.href='logout.php';">
          <?php endif; ?>
        </header>
      </section>
    <div class="container1">
        <div class="landing-page">
            <h1 class="big-title" style="font-family: Comic Sans MS">CY ORIENT - ADMIN</h1>
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
                // Add user message to chat
                addMessage(message, 'user-message');
                userInput.value = '';
                
                // Here you would typically send the message to your chatbot backend
                // For now, we'll just simulate a bot response
                setTimeout(() => {
                    const botResponses = [
                        "Je comprends votre demande. Pourriez-vous me donner plus de détails ?",
                        "Je peux vous aider avec les réservations, les informations sur les voyages, et plus encore !",
                        "Désolé, je n'ai pas compris. Pouvez-vous reformuler votre question ?",
                        "Pour les réservations, veuillez visiter notre page 'Réserver'.",
                        "Merci pour votre message. Un de nos conseillers vous répondra bientôt."
                    ];
                    const randomResponse = botResponses[Math.floor(Math.random() * botResponses.length)];
                    addMessage(randomResponse, 'bot-message');
                }, 1000);
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
    </script>
</body>
</html>