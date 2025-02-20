<?php
session_start();

// Overenie, či je admin prihlásený
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php"); // Presmerovanie na prihlasovaciu stránku
    exit;
}

// Pripojenie k databáze
require 'db_connect.php';

// Ak bolo stlačené tlačidlo "Odoslané"
if (isset($_POST['mark_as_sent'])) {
    $order_id = $_POST['order_id'];
    
    // Vymazanie záznamov z order_items
    $delete_items_sql = "DELETE FROM order_items WHERE order_id = ?";
    $stmt_items = $conn->prepare($delete_items_sql);
    $stmt_items->bind_param("i", $order_id);
    $stmt_items->execute();

    // Vymazanie záznamov z orders
    $delete_order_sql = "DELETE FROM orders WHERE id = ?";
    $stmt_order = $conn->prepare($delete_order_sql);
    $stmt_order->bind_param("i", $order_id);
    $stmt_order->execute();
}

// Získanie všetkých objednávok
$sql = "SELECT orders.id, users.name, users.email, orders.total_price, orders.order_date, orders.payment_method, 
               orders.street, orders.city, orders.postal_code, orders.country, orders.phone 
        FROM orders 
        JOIN users ON orders.user_id = users.id";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Objednávky</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <h1>ADMIN PANEL</h1>
        </div>
        <nav>
            <a href="admin_panel.php"><i class="icon">📊</i> Dashboard</a>
            <a href="admin_products.php"><i class="icon">🛒</i> Správa produktov</a>
            <a href="admin_categories.php"><i class="icon">📂</i> Správa kategórií</a>
            <a href="admin_orders.php"><i class="icon">📦</i> Objednávky</a>
            <a href="admin_sklad.php"><i class="icon">🗃️</i> Sklad</a>
            <a href="admin_support.php"><i class="icon">💬</i> Zákaznícky Servis a Podpora</a>
            <a href="admin_logout.php"><i class="icon">🔒</i> Odhlásiť sa</a>
        </nav>
    </div>
    <div class="main-content">
        <header>
            <h1>Objednávky</h1>
            <p>Spravuj objednávky tvojho e-shopu jednoducho a efektívne.</p>
        </header>
        <div class="orders-table">
            <table>
                <thead>
                    <tr>
                        <th>ID objednávky</th>
                        <th>Meno zákazníka</th>
                        <th>Email zákazníka</th>
                        <th>Celková suma</th>
                        <th>Dátum objednávky</th>
                        <th>Spôsob platby</th>
                        <th>Produkty</th>
                        <th>Doručovacie údaje</th>
                        <th>Odoslané</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            // Získanie položiek pre aktuálnu objednávku
                            $order_id = $row['id'];
                            $items_sql = "SELECT product_name, quantity, price FROM order_items WHERE order_id = $order_id";
                            $items_result = $conn->query($items_sql);

                            // Zoznam položiek
                            $items_list = "";
                            if ($items_result->num_rows > 0) {
                                while ($item = $items_result->fetch_assoc()) {
                                    $items_list .= $item['product_name'] . " (Množstvo: " . $item['quantity'] . ", Cena: " . $item['price'] . "€)<br>";
                                }
                            } else {
                                $items_list = "Žiadne položky";
                            }

                            // Doručovacie údaje
                            $delivery_info = $row['street'] . ", " . $row['city'] . ", " . $row['postal_code'] . ", " . $row['country'] . "<br>" . "Tel.: " . $row['phone'];

                            // Formátovanie spôsobu platby
                            $payment_method = $row['payment_method'];
                            if ($payment_method == 'bank_transfer') {
                                $payment_method = 'Prevod na účet';
                            } elseif ($payment_method == 'cash_on_delivery') {
                                $payment_method = 'Na dobierku';
                            }

                            echo "<tr>";
                            echo "<td>" . $row['id'] . "</td>";
                            echo "<td>" . $row['name'] . "</td>";
                            echo "<td>" . $row['email'] . "</td>";
                            echo "<td>" . $row['total_price'] . "</td>";
                            echo "<td>" . $row['order_date'] . "</td>";
                            echo "<td>" . $payment_method . "</td>";
                            echo "<td>" . $items_list . "</td>";
                            echo "<td>" . $delivery_info . "</td>";
                            echo "<td>
                                    <form method='post' action=''>
                                        <input type='hidden' name='order_id' value='" . $row['id'] . "'>
                                        <button type='submit' name='mark_as_sent'>Odoslané</button>
                                    </form>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='9'>Žiadne objednávky</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
