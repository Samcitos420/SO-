<?php
// Tento súbor sa používa na načítanie údajov o košíku (produkty a celková cena).

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
            WHERE ci.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
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
            $sql = "SELECT name, price FROM products WHERE product_id = ?";
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

// Vrátíme údaje o košíku, aby sa mohli použiť v index.php
return $cart_data;
?>
