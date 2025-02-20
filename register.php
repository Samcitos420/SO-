<?php
session_start(); // Spustenie session na začiatku

$servername = "localhost";
$username = "root"; // Predvolené meno pre XAMPP
$password = ""; // Predvolené heslo pre XAMPP
$dbname = "befitshop";

// Vytvorenie pripojenia
$conn = new mysqli($servername, $username, $password, $dbname);

// Skontroluj pripojenie
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Získanie údajov z formulára
$email = $_POST['email'];
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

// Validácia hesiel
if ($password !== $confirm_password) {
    die("Heslá sa nezhodujú.");
}

// SQL dotaz na vloženie užívateľa
$stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
$stmt->bind_param("ss", $email, $password);

if ($stmt->execute()) {
    header("Location: index.php");
    exit();
} else {
    echo "Chyba: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
