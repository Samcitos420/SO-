<?php
session_start(); // Spusti session pre ukladanie informácií o prihlásenom používateľovi

// Skontroluj, či je formulár odoslaný
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Skontroluj, či sú všetky požadované údaje
    if (isset($_POST['name'], $_POST['email'], $_POST['message'])) {
        // Pripojenie na databázu
        $servername = "localhost";
        $username = "root";  // Zmeň na svoje prihlasovacie údaje
        $password = "";      // Zmeň na svoje prihlasovacie údaje
        $dbname = "befitshop";  // Názov tvojej databázy

        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Očisti a ulož údaje z formulára
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $message = mysqli_real_escape_string($conn, $_POST['message']);

        // Vloženie správy do databázy
        $stmt = $conn->prepare("INSERT INTO support_messages (name, email, message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $message);
        $stmt->execute();
        $stmt->close();

        // Zatvorenie spojenia s databázou
        $conn->close();

        // Presmeruj na stránku s potvrdením úspechu
        header('Location: support.php?status=success');
        exit();
    } else {
        // Ak chýbajú údaje, presmeruj späť s chybovou správou
        header('Location: support.php?status=error');
        exit();
    }
}
?>
