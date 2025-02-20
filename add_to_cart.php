<?php
session_start();
include 'db_connect.php'; // Pripojenie k databáze

// Inicializácia premenných pre údaje o košíku
$cart_data = ['cart_items' => [], 'total_price' => 0];

// Ak je používateľ prihlásený, načítaj položky z databázy
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT ci.product_id, p.name, ci.quantity, (ci.quantity * p.price) AS total
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.product_id
            WHERE ci.id = ?"; // Používa sa id na identifikáciu prihláseného používateľa
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id); // Parametrizované viazanie pre id používateľa
    $stmt->execute();
    $result = $stmt->get_result();

    $cart_items = [];
    $total_price = 0;
    while ($row = $result->fetch_assoc()) {
        $cart_items[] = $row;
        $total_price += $row['total'];
    }
    $cart_data['cart_items'] = $cart_items;
    $cart_data['total_price'] = $total_price;
} else {
    // Ak nie je používateľ prihlásený, načítaj položky z session
    if (isset($_SESSION['cart'])) {
        $cart_items = [];
        $total_price = 0;
        foreach ($_SESSION['cart'] as $item) {
            // Získať produkt z databázy na základe product_id
            $sql = "SELECT name, price FROM products WHERE product_id = ?"; // Zmena názvu stĺpca na product_id
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $item['product_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $product = $result->fetch_assoc();

            $cart_items[] = [
                'name' => $product['name'],
                'quantity' => $item['quantity'],
                'total' => $item['quantity'] * $product['price']
            ];
            $total_price += $item['quantity'] * $product['price'];
        }
        $cart_data['cart_items'] = $cart_items;
        $cart_data['total_price'] = $total_price;
    }
}

// Spracovanie pridania produktu do košíka
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    // Ak je používateľ prihlásený, ulož košík do databázy
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];

        // Over, či už produkt existuje v košíku
        $sql = "SELECT * FROM cart_items WHERE id = ? AND product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $product_id); // Parametrizované viazanie pre id používateľa a product_id
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Aktualizuj množstvo produktu v košíku
            $sql = "UPDATE cart_items SET quantity = quantity + ? WHERE id = ? AND product_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iii", $quantity, $user_id, $product_id);
        } else {
            // Vlož nový produkt do košíka
            $sql = "INSERT INTO cart_items (id, product_id, quantity) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iii", $user_id, $product_id, $quantity);
        }
        $stmt->execute();
        $stmt->close();
    } else {
        // Ak nie je používateľ prihlásený, ulož košík do session
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Získaj session_id a pridaj ho do košíka
        $session_id = session_id();

        // Pridaj produkt do session košíka
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['product_id'] == $product_id) {
                $item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $_SESSION['cart'][] = ['session_id' => $session_id, 'product_id' => $product_id, 'quantity' => $quantity];
        }
    }

    echo json_encode(['message' => 'Produkt bol pridaný do košíka.']);
} else {
    echo json_encode(['error' => 'Neplatná požiadavka.']);
}
?>
