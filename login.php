<?php
session_start(); // Spusti session pre ukladanie informácií o prihlásenom používateľovi

$servername = "localhost";
$username = "root"; // Predvolené meno pre XAMPP
$password = ""; // Predvolené heslo pre XAMPP
$dbname = "befitshop";

// Vytvorenie pripojenia k databáze
$conn = new mysqli($servername, $username, $password, $dbname);

// Skontroluj pripojenie
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Získanie údajov z prihlasovacieho formulára
$email = $_POST['email'];
$password = $_POST['password'];

// Skontrolovanie, či existuje používateľ s týmto e-mailom
$stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// Ak používateľ existuje, porovnaj heslo
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $stored_password = $row['password'];

    // Porovnanie hesla bez hashovania (nie bezpečné)
    if ($password === $stored_password) {
        // Prihlásenie úspešné
        $_SESSION['user_id'] = $row['id']; // Ulož ID používateľa do session
        // Presmeruj na hlavnú stránku alebo inú stránku po prihlásení
        header("Location: index.php");
        exit();
    } else {
        echo "Nesprávne heslo.";
    }
} else {
    echo "Používateľ s týmto e-mailom neexistuje.";
}

$stmt->close();
$conn->close();
?>
