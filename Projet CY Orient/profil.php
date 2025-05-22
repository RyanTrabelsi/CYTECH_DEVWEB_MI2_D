<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login_form.php");
    exit();
}

// Database connection
$host = 'localhost';
$dbname = 'cy_orient';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$message = '';
$success = false;

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get user ID from session
    $user_id = $_SESSION['user_id'];
    
    // Prepare update statement based on which field was submitted
    if (isset($_POST['update_name'])) {
        $name = $conn->real_escape_string($_POST['name']);
        $stmt = $conn->prepare("UPDATE users SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $name, $user_id);
    } 
    elseif (isset($_POST['update_surname'])) {
        $surname = $conn->real_escape_string($_POST['surname']);
        $stmt = $conn->prepare("UPDATE users SET surname = ? WHERE id = ?");
        $stmt->bind_param("si", $surname, $user_id);
    }
    elseif (isset($_POST['update_genre'])) {
        $genre = $conn->real_escape_string($_POST['genre']);
        $stmt = $conn->prepare("UPDATE users SET genre = ? WHERE id = ?");
        $stmt->bind_param("si", $genre, $user_id);
    }
    elseif (isset($_POST['update_contact'])) {
        $contact_info = $conn->real_escape_string($_POST['contact_info']);
        $stmt = $conn->prepare("UPDATE users SET contact_info = ? WHERE id = ?");
        $stmt->bind_param("si", $contact_info, $user_id);
    }
    elseif (isset($_POST['update_adresse'])) {
        $adresse = $conn->real_escape_string($_POST['adresse']);
        $stmt = $conn->prepare("UPDATE users SET adresse = ? WHERE id = ?");
        $stmt->bind_param("si", $adresse, $user_id);
    }
    elseif (isset($_POST['update_nationalite'])) {
        $nationalite = $conn->real_escape_string($_POST['nationalite']);
        $stmt = $conn->prepare("UPDATE users SET nationalite = ? WHERE id = ?");
        $stmt->bind_param("si", $nationalite, $user_id);
    }
    elseif (isset($_POST['update_naissance'])) {
        $date_naissance = $conn->real_escape_string($_POST['date_naissance']);
        $stmt = $conn->prepare("UPDATE users SET date_naissance = ? WHERE id = ?");
        $stmt->bind_param("si", $date_naissance, $user_id);
    }
    
    if (isset($stmt)) {
        if ($stmt->execute()) {
            $message = "Informations mises à jour avec succès!";
            $success = true;
        } else {
            $message = "Erreur lors de la mise à jour: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Get current user data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon profil</title>
    <link rel="stylesheet" type="text/css" href="style.css"> 
    <style>
        .edit-form {
            display: none;
            margin-top: 10px;
        }
        .success-message {
            color: green;
            text-align: center;
            margin: 10px 0;
        }
        .error-message {
            color: red;
            text-align: center;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <!-- En-tête -->
    <header class="header">
        <img src="image/logo.png">
        <nav class="nav1">
          <li><a href="accueil.php">Accueil</a></li>
          <li><a href="presentation.php">Présentation</a></li>
          <li><a href="reserver.php">Réserver</a></li>
          <li><a href="profil.php">Mon compte</a></li>
        </nav>
        <input type="submit" value="Se déconnecter" name="deconnexion" id="deconnexion" onclick="window.location.href='logout.php';">
    </header>

    <div class="m2">
        <?php if ($message): ?>
            <div class="<?php echo $success ? 'success-message' : 'error-message'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <h2>Informations personnelles</h2>
        <p>Mettez à jour vos informations</p>
        <img src="image/pp.png" alt="Image " class="img2">
        <hr class="ligne">

        <!-- Nom -->
        <div class="m4">
            <nav class="info">
                <p><b>Nom :</b></p>
                <p><?php echo htmlspecialchars($user['name'] ?? '(votre nom)'); ?></p>
            </nav>
            <a href="#" onclick="toggleEdit('edit-name')">Modifier</a>
            <div id="edit-name" class="edit-form">
                <form method="post">
                    <input type="text" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                    <input type="submit" name="update_name" value="Enregistrer">
                    <button type="button" onclick="toggleEdit('edit-name')">Annuler</button>
                </form>
            </div>
        </div>

        <hr class="ligne">

        <!-- Prénom -->
        <div class="m4">
            <nav class="info">
                <p><b>Prénom :</b></p>
                <p><?php echo htmlspecialchars($user['surname'] ?? '(votre prénom)'); ?></p>
            </nav>
            <a href="#" onclick="toggleEdit('edit-surname')">Modifier</a>
            <div id="edit-surname" class="edit-form">
                <form method="post">
                    <input type="text" name="surname" value="<?php echo htmlspecialchars($user['surname'] ?? ''); ?>" required>
                    <input type="submit" name="update_surname" value="Enregistrer">
                    <button type="button" onclick="toggleEdit('edit-surname')">Annuler</button>
                </form>
            </div>
        </div>

        <hr class="ligne">

        <!-- Genre -->
        <div class="m4">
            <nav class="info">
                <p><b>Genre :</b></p>
                <p><?php echo htmlspecialchars($user['genre'] ?? '(votre genre)'); ?></p>
            </nav>
            <a href="#" onclick="toggleEdit('edit-genre')">Modifier</a>
            <div id="edit-genre" class="edit-form">
                <form method="post">
                    <select name="genre" required>
                        <option value="">Sélectionnez...</option>
                        <option value="Homme" <?php echo (isset($user['genre']) && $user['genre'] === 'Homme') ? 'selected' : ''; ?>>Homme</option>
                        <option value="Femme" <?php echo (isset($user['genre']) && $user['genre'] === 'Femme') ? 'selected' : ''; ?>>Femme</option>
                        <option value="Autre" <?php echo (isset($user['genre']) && $user['genre'] === 'Autre') ? 'selected' : ''; ?>>Autre</option>
                    </select>
                    <input type="submit" name="update_genre" value="Enregistrer">
                    <button type="button" onclick="toggleEdit('edit-genre')">Annuler</button>
                </form>
            </div>
        </div>

        <hr class="ligne">
        
        <!-- Contact Info -->
        <div class="m4">
            <nav class="info">
                <p><b><?php echo (filter_var($user['contact_info'], FILTER_VALIDATE_EMAIL) ? 'Adresse e-mail' : 'Numéro de téléphone'); ?> :</b></p>
                <p><?php echo htmlspecialchars($user['contact_info'] ?? '(votre contact)'); ?></p>
            </nav>
            <a href="#" onclick="toggleEdit('edit-contact')">Modifier</a>
            <div id="edit-contact" class="edit-form">
                <form method="post">
                    <input type="text" name="contact_info" value="<?php echo htmlspecialchars($user['contact_info'] ?? ''); ?>" required>
                    <input type="submit" name="update_contact" value="Enregistrer">
                    <button type="button" onclick="toggleEdit('edit-contact')">Annuler</button>
                </form>
            </div>
        </div>

        <hr class="ligne">

        <!-- Adresse -->
        <div class="m4">
            <nav class="info">
                <p><b>Adresse :</b></p>
                <p><?php echo htmlspecialchars($user['adresse'] ?? '(votre adresse)'); ?></p>
            </nav>
            <a href="#" onclick="toggleEdit('edit-adresse')">Modifier</a>
            <div id="edit-adresse" class="edit-form">
                <form method="post">
                    <textarea name="adresse" required><?php echo htmlspecialchars($user['adresse'] ?? ''); ?></textarea>
                    <input type="submit" name="update_adresse" value="Enregistrer">
                    <button type="button" onclick="toggleEdit('edit-adresse')">Annuler</button>
                </form>
            </div>
        </div>
        
        <hr class="ligne">

        <!-- Nationalité -->
        <div class="m4">
            <nav class="info">
                <p><b>Nationalité :</b></p>
                <p><?php echo htmlspecialchars($user['nationalite'] ?? '(votre nationalité)'); ?></p>
            </nav>
            <a href="#" onclick="toggleEdit('edit-nationalite')">Modifier</a>
            <div id="edit-nationalite" class="edit-form">
                <form method="post">
                    <input type="text" name="nationalite" value="<?php echo htmlspecialchars($user['nationalite'] ?? ''); ?>" required>
                    <input type="submit" name="update_nationalite" value="Enregistrer">
                    <button type="button" onclick="toggleEdit('edit-nationalite')">Annuler</button>
                </form>
            </div>
        </div>

        <hr class="ligne">
        
        <!-- Date de naissance -->
        <div class="m4">
            <nav class="info">
                <p><b>Date de naissance :</b></p>
                <p><?php echo isset($user['date_naissance']) ? htmlspecialchars($user['date_naissance']) : '(votre date de naissance)'; ?></p>
            </nav>
            <a href="#" onclick="toggleEdit('edit-naissance')">Modifier</a>
            <div id="edit-naissance" class="edit-form">
                <form method="post">
                    <input type="date" name="date_naissance" value="<?php echo isset($user['date_naissance']) ? htmlspecialchars($user['date_naissance']) : ''; ?>" required>
                    <input type="submit" name="update_naissance" value="Enregistrer">
                    <button type="button" onclick="toggleEdit('edit-naissance')">Annuler</button>
                </form>
            </div>
        </div>

        <hr class="ligne">
    </div>

    <script>
        function toggleEdit(formId) {
            const form = document.getElementById(formId);
            form.style.display = form.style.display === 'block' ? 'none' : 'block';
        }
    </script>
</body>
</html>