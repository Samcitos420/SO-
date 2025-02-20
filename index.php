<?php
$cart_data = include('get_cart_data.php');
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
                        <li><a href="moj-ucet.php">Môj účet</a></li>
                        <li><a href="moje-objednavky.php">Moje objednávky</a></li>
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
        <h1>Buď fit, zostaň zdravý</h1>
        <p>Široký výber fitness vybavenia, tréningového oblečenia a doplnkov pre tvoje zdravie.</p>
        <a href="fitness-pomocky.html" class="cta-button">Preskúmať produkty</a>
    </div>
</section>

<div class="container">
    <h2>Naša ponuka fitness produktov</h2>
    <section class="products-section">
        <div class="product-card">
            <h3>Športová výživa</h3>
            <p>Zvýšte svoj výkon s našimi doplnkami.</p>
            <a href="sportova_vyziva.php" class="cta-button-small">Zobraziť viac</a>
        </div>
        <div class="product-card">
            <h3>Športové oblečenie</h3>
            <p>Pohodlné a štýlové športové oblečenie.</p>
            <a href="sportove_oblecenie.php" class="cta-button-small">Zobraziť viac</a>
        </div>
        <div class="product-card">
            <h3>Príslušenstvo</h3>
            <p>Špičkové príslušenstvo pre každý tréning.</p>
            <a href="prislusenstvo.php" class="cta-button-small">Zobraziť viac</a>
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
