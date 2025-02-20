<?php
session_start(); // Spusti session pre ukladanie informácií o prihlásenom používateľovi
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>BeFitShop</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>

<header>
<div class="logo">
        <h1><a href="index.php" class="logo">BeFitShop</a></h1>
    </div>
    <nav>
        <img src="shopping-bag.png" alt="Nákupný košík" class="nav-icon">
        <div class="user-icon-container">
            <img src="user.png" alt="Prihlásenie" class="nav-icon" id="userIcon" onclick="toggleUserMenu()">

            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- Tento div sa zobrazí po prihlásení -->
                <div class="login-form" id="loginForm">
                    <ul>
                        <li><a href="moj-ucet.html">Môj účet</a></li>
                        <li><a href="moje-objednavky.html">Moje objednávky</a></li>
                        <li><a href="logout.php">Odhlásiť sa</a></li> <!-- Link na odhlásenie -->
                    </ul>
                </div>
            <?php else: ?>
                <!-- Formulár pre prihlásenie, ktorý sa zobrazí, ak nie je používateľ prihlásený -->
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
            <h1>Športová výživa</h1>
            <p>Posilnite svoje telo s našimi kvalitnými doplnkami.</p>
        </div>
    </section>

    <div class="container">
        <h2>Naše najlepšie výživové doplnky</h2>
        <section class="products-section">
            <div class="product-card">
                <h3>Proteínový prášok</h3>
                <p>Skvelý pre regeneráciu po tréningu.</p>
                <a href="protein.php" class="cta-button-small">Zobraziť viac</a>
            </div>
            <div class="product-card">
                <h3>Vitamíny</h3>
                <p>Pre celkové zdravie a pohodu.</p>
                <a href="#" class="cta-button-small">Zobraziť viac</a>
            </div>
        </section>
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
    // Získanie informácie o tom, či je používateľ prihlásený
    const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;

    // Funkcia na zobrazenie a skrytie prvkov na základe stavu prihlásenia
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

    // Funkcia na prepínanie viditeľnosti používateľského menu
    function toggleUserMenu() {
    const userMenu = document.getElementById('userMenu');
    userMenu.classList.toggle('hidden');
}

    // Zavrieť menu pri kliknutí mimo neho
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
