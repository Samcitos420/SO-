<?php
// Inicializácia pre správy
$status_message = '';

// Skontroluj, či je formulár odoslaný
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Skontroluj, či sú všetky požiadavky
    if (isset($_POST['support_name'], $_POST['email'], $_POST['message'])) {
        // Pripojenie na databázu
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "befitshop";

        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Očistíme a uložíme údaje
        $support_name = mysqli_real_escape_string($conn, $_POST['support_name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $message = mysqli_real_escape_string($conn, $_POST['message']);

        // Vložíme údaje do databázy
        $stmt = $conn->prepare("INSERT INTO support_messages (support_name, email, message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $support_name, $email, $message);
        if ($stmt->execute()) {
            // Po úspešnom vložení nastavíme správu o úspechu
            $status_message = 'Vaša správa bola úspešne odoslaná!';
        } else {
            // V prípade chyby nastavíme správu o chybe
            $status_message = 'Došlo k chybe pri odosielaní správy. Skúste to znova.';
        }

        // Uzavrieme pripojenie
        $stmt->close();
        $conn->close();
    } else {
        // Ak nie sú vyplnené všetky polia, nastavíme chybovú správu
        $status_message = 'Všetky polia musia byť vyplnené.';
    }
}
?>


<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Zákaznícka podpora | BeFitShop</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>

<header>
    <div class="logo">
        <h1><a href="index.php" class="logo">BeFitShop</a></h1>
    </div>
    <nav>
        <!-- Ikona nákupného košíka -->
        <div class="cart-icon-container">
            <img src="shopping-bag.png" alt="Nákupný košík" class="nav-icon" onmouseover="showCart()" onmouseout="hideCart()">
            <div class="cart-tooltip" onmouseover="keepCartVisible()" onmouseout="hideCart()">
                <h4>Váš nákupný košík</h4>
                <?php if (!empty($cart_data['cart_items'])): ?>
                    <ul>
                        <?php foreach ($cart_data['cart_items'] as $item): ?>
                            <li>
                                <?php echo htmlspecialchars($item['name']); ?> - 
                                <?php echo $item['quantity']; ?> ks - 
                                <?php echo number_format($item['total'], 2); ?> €
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <p><strong>Celkom: <?php echo number_format($cart_data['total_price'], 2); ?> €</strong></p>
                <?php else: ?>
                    <p>Košík je prázdny.</p>
                <?php endif; ?>
                <a href="cart.php" class="view-cart-btn">Zobraziť košík</a>
            </div>
        </div>
        <!-- Ikona používateľa -->
        <div class="user-icon-container">
            <img src="user.png" alt="Prihlásenie" class="nav-icon" id="userIcon" onclick="toggleUserMenu()">

            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- Menu pre prihláseného používateľa -->
                <div class="login-form" id="loginForm">
                    <ul>
                        <li><a href="moj-ucet.html">Môj účet</a></li>
                        <li><a href="moje-objednavky.html">Moje objednávky</a></li>
                        <li><a href="logout.php">Odhlásiť sa</a></li>
                    </ul>
                </div>
            <?php else: ?>
                <!-- Formulár pre neprihláseného používateľa -->
                <div class="login-form" id="loginForm">
                    <form action="login.php" method="POST">
                        <h3>Prihlásenie</h3>
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required>
                        
                        <label for="password">Heslo:</label>
                        <input type="password" id="password" name="password" required>
                        
                        <button type="submit">Prihlásiť sa</button>
                    </form>
                    <p>Nemáš ešte účet?</p>
                    <button type="button" onclick="openRegistrationModal()">Zaregistruj sa</button>
                </div>
            <?php endif; ?>
        </div>
    </nav>
</header>

<section class="hero">
    <div class="hero-content">
        <h2>Zákaznícka podpora</h2>
        <p>Sme tu pre vás, aby sme vám pomohli s akýmkoľvek problémom alebo otázkou.</p>
    </div>
</section>

<div class="container">
    <div class="container-support">
        <h3>Kontaktujte nás</h3>
        <ul>
            <li><strong>Telefón:</strong> +421 950 897 622</li>
            <li><strong>Email:</strong> support@befitshop.sk</li>
            <li><strong>Adresa:</strong> Mäsiarská 5 040 01 Košice</li>
            <li><strong>Otváracie hodiny:</strong> Pondelok - Piatok, 8:00 - 16:00</li>
        </ul>
    </div>

    <form action="support.php" method="POST" class="support-form">
    <h3>Odoslať správu</h3>
    <?php if ($status_message): ?>
        <p class="status-message"><?php echo htmlspecialchars($status_message); ?></p>
    <?php endif; ?>
    
    <label for="support_name">Vaše meno:</label>
    <input type="text" id="support_name" name="support_name" required>

    <label for="email">Váš email:</label>
    <input type="email" id="email" name="email" required>

    <label for="message">Vaša správa:</label>
    <textarea id="message" name="message" rows="5" required></textarea>

    <button type="submit" class="cta-button-small">Odoslať správu</button>
</form>
<div class="container-support">
        <a href="index.php" class="cta-button-small">Pokračovať v nakupovaní</a>
    </div>

<!-- Modálne okno pre registráciu -->
<div id="registrationModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeRegistrationModal()">&times;</span>
        <form id="registrationForm" class="registration-form" action="register.php" method="POST" onsubmit="return validateForm()">
            <h2 class="form-title">Registrácia účtu</h2>
            <label for="reg_email">Email:</label>
            <input type="email" id="reg_email" name="email" required>
            
            <label for="reg_password">Heslo:</label>
            <input type="password" id="reg_password" name="password" required>
            
            <label for="confirm_password">Potvrdiť heslo:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        
            <button type="submit" class="cta-button-small">Zaregistrovať sa</button>
        </form>
    </div>
</div>

<footer>
    <p>&copy; 2024 BeFitShop - Všetky práva vyhradené</p>
    <div class="social-icons">
        <a href="#"><i class="fab fa-facebook-f"></i></a>
        <a href="#"><i class="fab fa-twitter"></i></a>
        <a href="#"><i class="fab fa-instagram"></i></a>
    </div>
    <a href="support.php" class="support-link">Zákaznický servis a podpora</a>
</footer>

<script src="script.js"></script>
<script>
    const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;

    window.onload = function() {
        const userMenu = document.getElementById('userMenu');
        const loginForm = document.getElementById('loginForm');

        if (isLoggedIn) {
            userMenu.classList.remove('hidden');
            loginForm.classList.add('hidden');
        } else {
            userMenu.classList.add('hidden');
            loginForm.classList.remove('hidden');
        }
    }

    function toggleUserMenu() {
        const userMenu = document.getElementById('userMenu');
        userMenu.classList.toggle('hidden');
    }

    window.onclick = function(event) {
        const userMenu = document.getElementById('userMenu');
        if (!event.target.matches('#userIcon')) {
            if (!userMenu.classList.contains('hidden')) {
                userMenu.classList.add('hidden');
            }
        }
    }
</script>
</body>
</html>
