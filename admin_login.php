<?php
session_start();

// Simulované správne prihlasovacie údaje
$admin_username = "admin";
$admin_password = "admin";

// Spracovanie formulára
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($username === $admin_username && $password === $admin_password) {
        // Prihlásenie úspešné
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin_panel.php"); // Presmerovanie do admin panela
        exit;
    } else {
        $error_message = "Nesprávne meno alebo heslo.";
    }
}
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
    <div class="login-container">
        <h2>Prihlásenie do admin panela</h2>
        <?php if (isset($error_message)): ?>
            <p class="error"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <label for="username">Meno:</label>
            <input type="text" id="username" name="username" required>
            
            <label for="password">Heslo:</label>
            <input type="password" id="password" name="password" required>
            
            <button type="submit">Prihlásiť sa</button>
        </form>

        <!-- Tlačidlo na presmerovanie na e-shop -->
        <a href="index.php" class="back-to-shop-btn">Prejsť na e-shop</a>
    </div>
</body>
</html>
