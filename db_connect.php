<?php
// Nastavenia pripojenia k databáze
$servername = "localhost"; // Meno servera, zvyčajne localhost
$username = "root"; // Meno používateľa pre pripojenie k databáze (predvolený je 'root' pre XAMPP)
$password = ""; // Heslo pre používateľa 'root' (pre XAMPP je prázdne)
$dbname = "befitshop"; // Názov tvojej databázy

// Vytvorenie pripojenia
$conn = new mysqli($servername, $username, $password, $dbname);

// Skontrolovanie pripojenia
if ($conn->connect_error) {
    die("Pripojenie zlyhalo: " . $conn->connect_error);
}
?>
