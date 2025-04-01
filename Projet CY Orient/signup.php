<?php
// Database connection details
$host = 'localhost';
$dbname = 'cy_orient';
$username = 'root';
$password = '';

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$name = $conn->real_escape_string($_POST['name']);
$surname = $conn->real_escape_string($_POST['surname']);
$contact_info = $conn->real_escape_string($_POST['contact_info']);
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

// Use prepared statement to prevent SQL injection
$stmt = $conn->prepare("INSERT INTO users (name, surname, contact_info, password) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $name, $surname, $contact_info, $password);

if ($stmt->execute()) {
    echo "New record created successfully";
    header("Location: accueil.php");
} else {
    echo "Error: " . $stmt->error;
}

// Close the connection
$stmt->close();
$conn->close();
?>