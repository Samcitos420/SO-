<?php
$cart_data = include('get_cart_data.php');

// Pripojenie k databáze

// Načítanie ID kategórie z URL
$category_id = isset($_GET['id']) ? $_GET['id'] : 7;

// Načítanie produktov pre danú kategórie
$sql = "SELECT * FROM products WHERE category_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result = $stmt->get_result();

$products = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Určujeme, čo sa bude zobrazovať ako počet na sklade
        $stock = $row['stock'];

        if ($stock > 5) {
            $stock_text = "Na sklade > 5 ks";
            $stock_class = "green";  // Zelená farba
        } elseif ($stock == 5) {
            $stock_text = "Na sklade 5 ks";
            $stock_class = "green";  // Zelená farba
        } elseif ($stock == 4) {
            $stock_text = "Na sklade 4 ks";
            $stock_class = "green";  // Zelená farba
        } elseif ($stock == 3) {
            $stock_text = "Na sklade 3 ks";
            $stock_class = "green";  // Zelená farba
        } elseif ($stock == 2) {
            $stock_text = "Na sklade 2 ks";
            $stock_class = "orange";  // Oranžová farba
        } elseif ($stock == 1) {
            $stock_text = "Na sklade 1 ks";
            $stock_class = "red";  // Červená farba
        } elseif ($stock == 0) {
            $stock_text = "Momentálne nie je na sklade";
            $stock_class = "red";  // Červená farba
        }

        // Ukladáme všetky informácie o produkte
        $row['stock_text'] = $stock_text;
        $row['stock_class'] = $stock_class;
        $products[] = $row;
    }
}
$stmt->close();
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
        <div class="user-icon-container">
            <img src="user.png" alt="Prihlásenie" class="nav-icon" id="userIcon" onclick="toggleUserMenu()">

            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="login-form" id="loginForm">
                    <ul>
                        <li><a href="moj-ucet.html">Môj účet</a></li>
                        <li><a href="moje-objednavky.html">Moje objednávky</a></li>
                        <li><a href="logout.php">Odhlásiť sa</a></li>
                    </ul>
                </div>
            <?php else: ?>
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
        <h1>Proteínový prášok</h1>
        <p>Skvelý pre regeneráciu po tréningu.</p>
    </div>
</section>

<div class="products-list">
    <?php foreach ($products as $product): ?>
        <div class="product-item">
            <img src="<?php echo $product['image_path']; ?>" alt="<?php echo $product['name']; ?>">
            <h3><?php echo $product['name']; ?></h3>
            <p class="price"><?php echo number_format($product['price'], 2); ?> €</p>
            <p class="stock <?php echo $product['stock_class']; ?>">
                <?php echo $product['stock_text']; ?>
            </p> <!-- Zobrazenie počtu na sklade -->
            <button class="add-to-cart-btn" onclick="addToCart(<?php echo $product['product_id']; ?>)">Pridať do košíka</button>
        </div>
    <?php endforeach; ?>
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
