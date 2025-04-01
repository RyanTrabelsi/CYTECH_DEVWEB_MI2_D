<?php
session_start();

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
$contact_info = $_POST['contact_info'];
$password = $_POST['password'];

// Fetch user from the database
$sql = "SELECT id, password FROM users WHERE contact_info = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $contact_info);
$stmt->execute();
$stmt->store_result();

// Check if a user was found
if ($stmt->num_rows > 0) {
    $stmt->bind_result($id, $hashed_password);
    $stmt->fetch();

    // Verify the password
    if (password_verify($password, $hashed_password)) {
        // Password is correct, start a session
        $_SESSION['user_id'] = $id;
        $_SESSION['contact_info'] = $contact_info;

        // Redirect to the home page
        header("Location: accueil.php");
        exit();
    } else {
        // Password is incorrect
        $_SESSION['error'] = "Invalid contact information or password.";
        header("Location: login_form.php");
        exit();
    }
} else {
    // No user found with the provided contact info
    $_SESSION['error'] = "Invalid contact information or password.";
    header("Location: login_form.php");
    exit();
}

// Close the connection
$stmt->close();
$conn->close();
?>