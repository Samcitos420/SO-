<?php
session_start();
include 'db_connect.php';
// Overenie, či je admin prihlásený
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php"); // Presmerovanie na prihlasovaciu stránku
    exit;
}



// Počet produktov
$sql = "SELECT COUNT(*) as count FROM products";
$result = $conn->query($sql);
$product_count = ($result->num_rows > 0) ? $result->fetch_assoc()['count'] : 0;

// Počet kategórií
$sql = "SELECT COUNT(*) as count FROM categories";
$result = $conn->query($sql);
$category_count = ($result->num_rows > 0) ? $result->fetch_assoc()['count'] : 0;

// Počet objednávok
$sql = "SELECT COUNT(*) as count FROM orders";
$result = $conn->query($sql);
$order_count = ($result->num_rows > 0) ? $result->fetch_assoc()['count'] : 0;

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
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
            <h1>Vitaj v admin paneli</h1>
            <p>Spravuj svoj e-shop jednoducho a efektívne.</p>
        </header>
        <div class="dashboard-cards">
            <div class="card">
                <h2>Počet produktov</h2>
                <p><?php echo $product_count; ?></p>
            </div>
            <div class="card">
                <h2>Počet kategórií</h2>
                <p><?php echo $category_count; ?></p>
            </div>
            <div class="card">
                <h2>Počet objednávok</h2>
                <p><?php echo $order_count; ?></p>
            </div>
        </div>
    </div>
</body>
</html>
