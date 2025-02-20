<?php
session_start();

// Overenie, či je admin prihlásený
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php"); // Presmerovanie na prihlasovaciu stránku
    exit;
}

// Pripojenie k databáze
$conn = new mysqli("localhost", "root", "", "befitshop");
if ($conn->connect_error) {
    die("Pripojenie zlyhalo: " . $conn->connect_error);
}

// Načítanie kategórií
$categories = [];
$result = $conn->query("SELECT * FROM categories");
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

// Načítanie produktov
$products = [];
$result = $conn->query("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id");

while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

// Úprava produktu
$product_to_edit = null;
if (isset($_GET['edit'])) {
    $product_id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product_to_edit = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Uloženie úpravy produktu
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['edit_product'])) {
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];
    $product_price = $_POST['product_price'];
    $product_category = $_POST['product_category'];
    $product_stock = intval($_POST['product_stock']); // Získanie počtu kusov

    // Spracovanie obrázka (ak je nový)
    if (!empty($_FILES['product_image']['name'])) {
        $product_image = $_FILES['product_image']['name'];
        $target_dir = "images/";
        $target_file = $target_dir . basename($product_image);
        move_uploaded_file($_FILES['product_image']['tmp_name'], $target_file);
    } else {
        $target_file = $_POST['existing_image']; // Získať existujúci obrázok, ak nie je nový
    }

    // Uloženie upraveného produktu do databázy
    $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, image_path = ?, category_id = ?, stock = ? WHERE product_id = ?");
    $stmt->bind_param("sdsiii", $product_name, $product_price, $target_file, $product_category, $product_stock, $product_id);
    $stmt->execute();
    $stmt->close();

    // Obnovenie stránky po úprave produktu
    header("Location: admin_sklad.php");
    exit;
}

// Odstránenie produktu
if (isset($_GET['delete'])) {
    $product_id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->close();

    // Obnovenie stránky po odstránení
    header("Location: admin_sklad.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Správa produktov</title>
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
            <h1>Správa produktov</h1>
        </header>

        <!-- Zoznam produktov -->
        <div class="product-list">
            <h2>Existujúce produkty</h2>
            <div class="products">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <img src="<?php echo $product['image_path']; ?>" alt="<?php echo $product['name']; ?>">
                        <h3><?php echo $product['name']; ?></h3>
                        <p>Cena: <?php echo $product['price']; ?> €</p>
                        <p>Kategória: <?php echo $product['category_name'] ?: 'Nepriradená'; ?></p>
                        <p>Sklad: <?php echo $product['stock']; ?> kusov</p>
                        <a href="admin_sklad.php?edit=<?php echo $product['product_id']; ?>" class="edit-btn">Upraviť</a>
                        <a href="admin_sklad.php?delete=<?php echo $product['product_id']; ?>" class="delete-btn">Odstrániť</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if (isset($product_to_edit)): ?>
            <!-- Formulár na úpravu produktu -->
            <div id="editProductForm" class="add-product-form">
                <div class="form-container">
                    <h3>Upraviť produkt</h3>
                    <form method="POST" action="" enctype="multipart/form-data">
                        <input type="hidden" name="product_id" value="<?php echo $product_to_edit['product_id']; ?>">

                        <label for="product_name">Názov produktu:</label>
                        <input type="text" id="product_name" name="product_name" value="<?php echo $product_to_edit['name']; ?>" required>

                        <label for="product_price">Cena:</label>
                        <input type="text" id="product_price" name="product_price" value="<?php echo $product_to_edit['price']; ?>" required>

                        <label for="product_image">Obrázok produktu:</label>
                        <input type="file" id="product_image" name="product_image" accept="image/*">
                        <input type="hidden" name="existing_image" value="<?php echo $product_to_edit['image_path']; ?>">

                        <label for="product_category">Kategória:</label>
                        <select id="product_category" name="product_category" required>
                            <option value="" disabled selected>Vyberte kategóriu</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['category_id']; ?>" <?php echo $category['category_id'] == $product_to_edit['category_id'] ? 'selected' : ''; ?>>
                                    <?php echo $category['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <label for="product_stock">Počet kusov na sklade:</label>
                        <input type="number" id="product_stock" name="product_stock" min="0" value="<?php echo $product_to_edit['stock']; ?>" required>

                        <button type="submit" name="edit_product" class="submit-btn">Upraviť produkt</button>
                    </form>
                    <button id="closeEditFormBtn" class="close-btn">Zavrieť</button>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        document.getElementById('closeEditFormBtn').addEventListener('click', function() {
        document.getElementById('editProductForm').style.display = 'none';
    });

    // Ak chcete otvoriť formulár, skontroluj, či sa zobrazuje správne:
    if (document.getElementById('editProductForm')) {
        document.getElementById('editProductForm').style.display = 'flex';
    }

    </script>
</body>
</html>
