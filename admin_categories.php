<?php
session_start();

// Overenie, či je admin prihlásený
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

// Spojenie s databázou
$servername = "localhost";
$username = "root";  // Zmeň na tvoje prihlasovacie údaje
$password = "";      // Zmeň na tvoje prihlasovacie údaje
$dbname = "befitshop";  // Názov tvojej databázy

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Spracovanie formulára na pridanie kategórie
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_category'])) {
    $new_category_name = $_POST['category_name'];
    $new_category_description = $_POST['category_description'];

    // Pridanie novej kategórie do databázy
    $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
    $stmt->bind_param("ss", $new_category_name, $new_category_description);
    $stmt->execute();
    $stmt->close();
}

// Spracovanie odstránenia kategórie
if (isset($_GET['delete'])) {
    $category_id = $_GET['delete'];

    // Odstránenie kategórie z databázy
    $stmt = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $stmt->close();
}

// Načítanie kategórií z databázy
$sql = "SELECT * FROM categories";
$result = $conn->query($sql);
$categories = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Správa kategórií</title>
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
            <h1>Správa kategórií</h1>
        </header>
        
        <!-- Tlačidlo na pridanie kategórie -->
        <button id="addCategoryBtn" class="primary-btn">Pridať novú kategóriu</button>
        
        <!-- Formulár na pridanie kategórie -->
        <div id="addCategoryForm" class="add-category-form">
            <div class="form-container">
                <h3>Pridať novú kategóriu</h3>
                <form method="POST" action="">
                    <label for="category_name">Názov kategórie:</label>
                    <input type="text" id="category_name" name="category_name" required>

                    <label for="category_description">Popis:</label>
                    <textarea id="category_description" name="category_description" rows="3" required></textarea>

                    <button type="submit" name="add_category" class="submit-btn">Pridať kategóriu</button>
                </form>
                <button id="closeFormBtn" class="close-btn">Zavrieť</button>
            </div>
        </div>

        <!-- Zoznam kategórií -->
        <table class="categories-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Názov</th>
                    <th>Popis</th>
                    <th>Akcie</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($category['category_id']); ?></td>
                        <td><?php echo htmlspecialchars($category['name']); ?></td>
                        <td><?php echo htmlspecialchars($category['description']); ?></td>
                        <td>
                            <a href="admin_categories.php?delete=<?php echo $category['category_id']; ?>" class="delete-btn">Odstrániť</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        // Zobrazenie formulára
        const addCategoryBtn = document.getElementById("addCategoryBtn");
        const addCategoryForm = document.getElementById("addCategoryForm");
        const closeFormBtn = document.getElementById("closeFormBtn");

        addCategoryBtn.addEventListener("click", () => {
            addCategoryForm.style.display = "block";
        });

        closeFormBtn.addEventListener("click", () => {
            addCategoryForm.style.display = "none";
        });
    </script>
</body>
</html>
