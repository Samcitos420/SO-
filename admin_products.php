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

// Pridanie produktu
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_product'])) {
    $product_name = $_POST['product_name'];
    $product_price = $_POST['product_price'];
    $product_category = $_POST['product_category'];
    $product_stock = intval($_POST['product_stock']); // Získanie počtu kusov

    // Spracovanie obrázka
    $product_image = $_FILES['product_image']['name'];
    $target_dir = "images/";
    $target_file = $target_dir . basename($product_image);
    move_uploaded_file($_FILES['product_image']['tmp_name'], $target_file);

    // Uloženie produktu do databázy
    $stmt = $conn->prepare("INSERT INTO products (name, price, image_path, category_id, stock) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sdsii", $product_name, $product_price, $target_file, $product_category, $product_stock);
    $stmt->execute();
    $stmt->close();

    // Obnovenie stránky po pridaní produktu
    header("Location: admin_products.php");
    exit;
}

// Načítanie produktov
$products = [];
$result = $conn->query("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id");

while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

// Odstránenie produktu
if (isset($_GET['delete'])) {
    $product_id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->close();

    // Obnovenie stránky po odstránení
    header("Location: admin_products.php");
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

        <!-- Tlačidlo na pridanie produktu -->
        <div class="add-product-button">
            <button id="addProductBtn" class="primary-btn">Pridať produkt</button>
        </div>

        <!-- Formulár na pridanie produktu -->
        <div id="addProductForm" class="add-product-form">
            <div class="form-container">
                <h3>Pridať nový produkt</h3>
                <form method="POST" action="" enctype="multipart/form-data">
                    <label for="product_name">Názov produktu:</label>
                    <input type="text" id="product_name" name="product_name" required>

                    <label for="product_price">Cena:</label>
                    <input type="text" id="product_price" name="product_price" required>

                    <label for="product_image">Obrázok produktu:</label>
                    <input type="file" id="product_image" name="product_image" accept="image/*">

                    <label for="product_category">Kategória:</label>
                    <select id="product_category" name="product_category" required>
                        <option value="" disabled selected>Vyberte kategóriu</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['category_id']; ?>">
                                <?php echo $category['name']; ?>
                            </option>
                        <?php endforeach; ?>
                        </select>
                    <button type="submit" name="add_product" class="submit-btn">Pridať produkt</button>
                </form>
                <button id="closeFormBtn" class="close-btn">Zavrieť</button>
            </div>
        </div>

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
                        <!-- Odstránil som počet kusov na sklade -->
                        <!-- Tlačidlo "Upraviť" je odstránené -->
                        <a href="admin_products.php?delete=<?php echo $product['product_id']; ?>" class="delete-btn">Odstrániť</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
        // Zobraziť/skryť formulár na pridanie produktu
        document.getElementById('addProductBtn').addEventListener('click', function() {
            document.getElementById('addProductForm').style.display = 'flex';
        });

        // Zavrieť formulár na pridanie produktu
        document.getElementById('closeFormBtn').addEventListener('click', function() {
            document.getElementById('addProductForm').style.display = 'none';
        });
    </script>
</body>
</html>
