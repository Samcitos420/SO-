<?php
session_start();
require_once 'db_connect.php'; // Pripojenie k databáze

// Skontroluj, či je používateľ prihlásený
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php'); // Presmeruj neprihláseného používateľa na hlavnú stránku
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

// Načítaj aktuálne údaje používateľa
$query = "SELECT name, phone, street, city, postal_code, country FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    // Ak údaje neexistujú (čo by nemalo nastať), inicializuj prázdne hodnoty
    $user = [
        'name' => '',
        'phone' => '',
        'street' => '',
        'city' => '',
        'postal_code' => '',
        'country' => ''
    ];
}

// Spracuj formulár na aktualizáciu údajov
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $street = $_POST['street'] ?? '';
    $city = $_POST['city'] ?? '';
    $postal_code = $_POST['postal_code'] ?? '';
    $country = $_POST['country'] ?? '';

    // Aktualizácia údajov v databáze
    $update_query = "UPDATE users SET name = ?, phone = ?, street = ?, city = ?, postal_code = ?, country = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("ssssssi", $name, $phone, $street, $city, $postal_code, $country, $user_id);

    if ($update_stmt->execute()) {
        $message = "Údaje boli úspešne aktualizované.";

        // Aktualizuj $user, aby sa v formulári zobrazili nové údaje
        $user['name'] = $name;
        $user['phone'] = $phone;
        $user['street'] = $street;
        $user['city'] = $city;
        $user['postal_code'] = $postal_code;
        $user['country'] = $country;
    } else {
        $message = "Chyba pri aktualizácii údajov.";
    }
}
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
    <h2>Vaše doručovacie údaje</h2>
    <p>Uistite sa, že vaše doručovacie údaje sú aktuálne.</p>
    </div>
</section>

<div class="container">
    <form action="" method="POST" class="support-form">
        <h3>Upraviť doručovacie údaje</h3>
        
        <?php if (!empty($message)): ?>
            <p class="status-message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <label for="name">Meno:</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>

        <label for="phone">Telefón:</label>
        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>

        <label for="street">Ulica a číslo:</label>
        <input type="text" id="street" name="street" value="<?php echo htmlspecialchars($user['street']); ?>" required>

        <label for="city">Mesto:</label>
        <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($user['city']); ?>" required>

        <label for="postal_code">PSČ:</label>
        <input type="text" id="postal_code" name="postal_code" value="<?php echo htmlspecialchars($user['postal_code']); ?>" required>

        <label for="country">Krajina:</label>
        <input type="text" id="country" name="country" value="<?php echo htmlspecialchars($user['country']); ?>" required>

        <button type="submit" class="cta-button-small">Uložiť zmeny</button>
    </form>

    <div class="container-support">
        <a href="index.php" class="cta-button-small">Pokračovať v nakupovaní</a>
    </div>
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
