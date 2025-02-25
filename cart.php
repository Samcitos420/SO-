<?php
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include 'get_cart_data.php'; // Načítanie údajov o košíku

// Skontrolovať, či je používateľ prihlásený
if (!isset($_SESSION['user_id'])) {
    die(json_encode(['error' => 'Používateľ nie je prihlásený.']));
}

$user_id = $_SESSION['user_id'];

// Načítanie údajov používateľa
$sql = "SELECT name, phone, street, city, postal_code, country, email FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $name = $user['name'];
    $phone = $user['phone'];
    $street = $user['street'];
    $city = $user['city'];
    $postal_code = $user['postal_code'];
    $country = $user['country'];
    $email = $user['email'];
} else {
    die(json_encode(['error' => 'Používateľské údaje neboli nájdené.']));
}

$order_confirmation = '';

// Spracovanie formulára
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['payment_method']) || empty($_POST['payment_method'])) {
        die(json_encode(['error' => 'Platobná metóda nebola zvolená.']));
    }

    $payment_method = $_POST['payment_method'];

    // Výpočet celkovej ceny
    $sql = "
        SELECT ci.product_id, ci.quantity, p.name as product_name, p.price 
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.product_id
        WHERE ci.id = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id); // Používateľské ID z relácie
    $stmt->execute();
    $result = $stmt->get_result();

    $total_price = 0;
    $cart_items = [];
    while ($row = $result->fetch_assoc()) {
        $total_price += $row['quantity'] * $row['price'];
        $cart_items[] = $row;
    }

    if ($total_price == 0) {
        die(json_encode(['error' => 'Košík je prázdny. Nie je možné vytvoriť objednávku.']));
    }

    // Vloženie objednávky
    $sql = "
        INSERT INTO orders (user_id, name, phone, street, city, postal_code, country, payment_method, total_price, order_date) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssssssd", $user_id, $name, $phone, $street, $city, $postal_code, $country, $payment_method, $total_price);

    if ($stmt->execute()) {
        $order_id = $stmt->insert_id; // ID vytvorenej objednávky

        // Vloženie položiek objednávky
        $sql = "
            INSERT INTO order_items (order_id, product_id, product_name, quantity, price)
            VALUES (?, ?, ?, ?, ?)
        ";
        $stmt = $conn->prepare($sql);
        foreach ($cart_items as $item) {
            $stmt->bind_param("iisid", $order_id, $item['product_id'], $item['product_name'], $item['quantity'], $item['price']);
            $stmt->execute();

            // Aktualizácia skladu v tabuľke products
            $sql_update_stock = "
                UPDATE products
                SET stock = stock - ?
                WHERE product_id = ?
            ";
            $stmt_update = $conn->prepare($sql_update_stock);
            $stmt_update->bind_param("ii", $item['quantity'], $item['product_id']);
            $stmt_update->execute();
        }

        // Vymazanie košíka
        $sql = "DELETE FROM cart_items WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        $order_confirmation = "Objednávka bola úspešne vytvorená! ID objednávky: " . $order_id;

        // Generovanie faktúry
        $invoice_text = "<h1>Faktura</h1>";
        $invoice_text .= "<p><strong>Objednávka ID:</strong> " . $order_id . "</p>";
        $invoice_text .= "<p><strong>Meno:</strong> " . $name . "</p>";
        $invoice_text .= "<p><strong>Adresa:</strong> " . $street . ", " . $city . ", " . $postal_code . ", " . $country . "</p>";
        $invoice_text .= "<p><strong>Telefón:</strong> " . $phone . "</p>";
        $invoice_text .= "<hr>";

        $invoice_text .= "<table>";
        $invoice_text .= "<tr><th>Produkt</th><th>Množstvo</th><th>Cena</th></tr>";

        foreach ($cart_items as $item) {
            $invoice_text .= "<tr><td>" . $item['product_name'] . "</td><td>" . $item['quantity'] . "</td><td>" . $item['price'] . "€</td></tr>";
        }

        $invoice_text .= "</table>";
        $invoice_text .= "<hr>";
        $invoice_text .= "<p><strong>Celková suma:</strong> " . $total_price . "€</p>";

        if ($payment_method == 'bank_transfer') {
            $invoice_text .= "<p>Prosím, pošlite čiastku " . $total_price . "€ na účet IBAN: SK0811000000002932511344 a do poznámky uveďte číslo ID objednávky: " . $order_id . ".</p>";
        } elseif ($payment_method == 'cash_on_delivery') {
            $invoice_text .= "<p>Očakávajte dodanie balíka kuriérom v najbližších dňoch. Prosím, pripravte si čiastku " . $total_price . "€ na úhradu pri doručení.</p>";
        }

        // Odosielanie e-mailu pomocou PHPMailer
        $mail = new PHPMailer(true);

        try {
            // Konfigurácia SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';  // Nahradiť SMTP serverom
            $mail->SMTPAuth = true;
            $mail->Username = 'befitshop.orders@gmail.com';  // Nahradiť vašim SMTP užívateľským menom
            $mail->Password = 'gsxfzywxsbrzfdlc';  // Nahradiť vašim SMTP heslom
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Príjemca
            $mail->setFrom('befitshop.orders@gmail.com', 'BeFitShop');
            $mail->addAddress($email);  // E-mail zákazníka

            // Obsah e-mailu
            $mail->isHTML(true);  // Nastaviť na true pre HTML formátovanie e-mailu
            $mail->Subject = 'Vasa faktura';
            $mail->Body    = $invoice_text;

            $mail->send();
        } catch (Exception $e) {
            $order_confirmation .= " E-mail sa nepodarilo odoslať. Chyba: {$mail->ErrorInfo}";
        }
    } else {
        $order_confirmation = "Chyba pri vytváraní objednávky: " . $stmt->error;
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

<!-- Sekcia nákupného košíka -->
<section class="cart-section">
<div class="order-confirmation">
            <?php echo $order_confirmation; ?> 
</div>
    <h2>Váš nákupný košík</h2>
    <div class="cart-container">
        <?php if (!empty($cart_data['cart_items'])): ?>
            <ul class="cart-list">
                <?php foreach ($cart_data['cart_items'] as $item): ?>
                    <li class="cart-item">
                        <span class="cart-item-name"><?php echo htmlspecialchars($item['name']); ?></span>
                        <span class="cart-item-quantity"><?php echo $item['quantity']; ?> ks</span>
                        <span class="cart-item-total"><?php echo number_format($item['total'], 2); ?> €</span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <p class="cart-total">Celková suma: <strong><?php echo number_format($cart_data['total_price'], 2); ?> €</strong></p>
        <?php else: ?>
            <p>Váš košík je momentálne prázdny.</p>
        <?php endif; ?>
    </div>
    <div class="cart-container">
    <!-- Zobraz údaje používateľa -->
    <div class="user-info">
        <h3>Vaše doručovacie údaje:</h3>
        <p><strong>Meno:</strong> <?php echo htmlspecialchars($name); ?></p>
        <p><strong>Telefón:</strong> <?php echo htmlspecialchars($phone); ?></p>
        <p><strong>Ulica:</strong> <?php echo htmlspecialchars($street); ?></p>
        <p><strong>Mesto:</strong> <?php echo htmlspecialchars($city); ?></p>
        <p><strong>PSČ:</strong> <?php echo htmlspecialchars($postal_code); ?></p>
        <p><strong>Krajina:</strong> <?php echo htmlspecialchars($country); ?></p>
        <a href="moj-ucet.php" class="edit-button">Upraviť</a>
    </div>

    <!-- Zvoľte platobnú metódu -->
    <div class="payment-method">
        <h3>Spôsob platby:</h3>
        <form action="cart.php" method="POST">
            <label for="payment_method">Vyberte spôsob platby:</label>
            <select name="payment_method" id="payment_method" class="styled-select" required>
                <option value="cash_on_delivery">Na dobierku</option>
                <option value="bank_transfer">Platba na účet</option>
            </select>
            <button type="submit" class="submit-btn">Potvrdiť objednávku</button>
        </form>
    </div>
</div>
</section>


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
