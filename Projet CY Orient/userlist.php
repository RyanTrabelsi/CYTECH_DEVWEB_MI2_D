<?php
session_start();

// Verify admin status
if (!isset($_SESSION['user_id']) || $_SESSION['contact_type'] !== 'admin') {
    header('Location: accueil.php');
    exit();
}

// Database connection
require_once 'db_connect.php';

// Handle user updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $contact_info = $_POST['contact_info'];
    $genre = $_POST['genre'];
    $adresse = $_POST['adresse'];
    $nationalite = $_POST['nationalite'];
    $date_naissance = $_POST['date_naissance'];
    $contact_type = $_POST['contact_type_value']; // Changed to use the hidden input value
    
    // Handle password change if provided
    if (!empty($_POST['new_password'])) {
        $hashed_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET name = ?, surname = ?, contact_info = ?, genre = ?, adresse = ?, nationalite = ?, date_naissance = ?, contact_type = ?, password = ? WHERE id = ?");
        $stmt->execute([$name, $surname, $contact_info, $genre, $adresse, $nationalite, $date_naissance, $contact_type, $hashed_password, $user_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, surname = ?, contact_info = ?, genre = ?, adresse = ?, nationalite = ?, date_naissance = ?, contact_type = ? WHERE id = ?");
        $stmt->execute([$name, $surname, $contact_info, $genre, $adresse, $nationalite, $date_naissance, $contact_type, $user_id]);
    }
    
    $_SESSION['success_message'] = "Utilisateur mis à jour avec succès!";
    header("Location: userlist.php");
    exit();
}

// Fetch all users
try {
    $stmt = $pdo->query("SELECT * FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching users: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Utilisateurs - Admin</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #2c3e50;
            border-bottom: 2px solid #eaaa37;
            padding-bottom: 10px;
        }
        
        .user-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .user-table th, .user-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .user-table th {
            background-color: #2c3e50;
            color: white;
        }
        
        .user-table tr:hover {
            background-color: #f9f9f9;
            cursor: pointer;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow-y: auto;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 8px;
            width: 60%;
            max-width: 600px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            position: relative;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .close-modal {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 24px;
            cursor: pointer;
            color: #777;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: 'Montserrat', sans-serif;
        }
        
        .toggle-admin {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 30px;
            margin-left: 15px;
        }
        
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 22px;
            width: 22px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .slider {
            background-color: #e74c3c;
        }
        
        input:checked + .slider:before {
            transform: translateX(30px);
        }
        
        .submit-btn {
            background-color: #2c3e50;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-family: 'Montserrat', sans-serif;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        
        .submit-btn:hover {
            background-color: #1a252f;
        }
        
        .success-message {
            color: #27ae60;
            padding: 10px;
            margin-bottom: 20px;
            background-color: #e8f5e9;
            border-radius: 4px;
            text-align: center;
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
            <input type="button" class="admin-button" value="Liste des utilisateurs" onclick="window.location.href='userlist.php';">
            <input type="submit" value="Se déconnecter" name="logout" id="logout" onclick="window.location.href='logout.php';">
        </header>
    </section>

    <div class="container">
        <h1>Liste des Utilisateurs</h1>
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="success-message">
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        
        <table class="user-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Contact</th>
                    <th>Type</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr onclick="openUserModal(<?php echo htmlspecialchars(json_encode($user)); ?>)" data-user-id="<?php echo $user['id']; ?>">
                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                    <td><?php echo htmlspecialchars($user['surname']); ?></td>
                    <td><?php echo htmlspecialchars($user['contact_info']); ?></td>
                    <td><?php echo htmlspecialchars($user['contact_type']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- User Edit Modal -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <h2>Modifier l'utilisateur</h2>
            <form id="userForm" method="post">
                <input type="hidden" name="user_id" id="modalUserId">
                <input type="hidden" name="contact_type_value" id="contactTypeValue" value="user">
                
                <div class="form-group">
                    <label for="modalName">Nom</label>
                    <input type="text" id="modalName" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="modalSurname">Prénom</label>
                    <input type="text" id="modalSurname" name="surname" required>
                </div>
                
                <div class="form-group">
                    <label for="modalContact">Contact (Email/Téléphone)</label>
                    <input type="text" id="modalContact" name="contact_info" required>
                </div>
                
                <div class="form-group">
                    <label for="modalGenre">Genre</label>
                    <select id="modalGenre" name="genre">
                        <option value="Homme">Homme</option>
                        <option value="Femme">Femme</option>
                        <option value="Autre">Autre</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="modalAdresse">Adresse</label>
                    <input type="text" id="modalAdresse" name="adresse">
                </div>
                
                <div class="form-group">
                    <label for="modalNationalite">Nationalité</label>
                    <input type="text" id="modalNationalite" name="nationalite">
                </div>
                
                <div class="form-group">
                    <label for="modalNaissance">Date de naissance</label>
                    <input type="date" id="modalNaissance" name="date_naissance">
                </div>
                
                <div class="toggle-admin">
                    <label>Type de compte:</label>
                    <label class="toggle-switch">
                        <input type="checkbox" id="modalContactType" onchange="updateContactType(this)">
                        <span class="slider"></span>
                    </label>
                    <span id="adminStatusText">Utilisateur</span>
                </div>
                
                <div class="form-group">
                    <label for="modalPassword">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
                    <input type="password" id="modalPassword" name="new_password">
                </div>
                
                <button type="submit" class="submit-btn">Enregistrer les modifications</button>
            </form>
        </div>
    </div>

    <script>
        let currentScrollPos = 0;
        
        function openUserModal(user) {
            // Save current scroll position
            currentScrollPos = window.pageYOffset || document.documentElement.scrollTop;
            
            const modal = document.getElementById('userModal');
            document.getElementById('modalUserId').value = user.id;
            document.getElementById('modalName').value = user.name || '';
            document.getElementById('modalSurname').value = user.surname || '';
            document.getElementById('modalContact').value = user.contact_info || '';
            document.getElementById('modalGenre').value = user.genre || 'Homme';
            document.getElementById('modalAdresse').value = user.adresse || '';
            document.getElementById('modalNationalite').value = user.nationalite || '';
            document.getElementById('modalNaissance').value = user.date_naissance || '';
            
            // Set admin toggle
            const isAdmin = user.contact_type === 'admin';
            document.getElementById('modalContactType').checked = isAdmin;
            document.getElementById('adminStatusText').textContent = isAdmin ? 'Administrateur' : 'Utilisateur';
            document.getElementById('contactTypeValue').value = isAdmin ? 'admin' : 'user';
            
            // Prevent body scroll
            document.body.style.overflow = 'hidden';
            document.body.style.position = 'fixed';
            document.body.style.width = '100%';
            
            modal.style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('userModal').style.display = 'none';
            
            // Restore body scroll
            document.body.style.overflow = '';
            document.body.style.position = '';
            document.body.style.width = '';
            
            // Restore scroll position
            window.scrollTo(0, currentScrollPos);
        }
        
        function updateContactType(checkbox) {
            const statusText = document.getElementById('adminStatusText');
            const hiddenInput = document.getElementById('contactTypeValue');
            
            if (checkbox.checked) {
                statusText.textContent = 'Administrateur';
                hiddenInput.value = 'admin';
            } else {
                statusText.textContent = 'Utilisateur';
                hiddenInput.value = 'user';
            }
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('userModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>