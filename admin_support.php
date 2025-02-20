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

// Spracovanie odstránenia správy
if (isset($_GET['delete'])) {
    $support_id = $_GET['delete'];

    // Odstránenie správy z databázy
    $stmt = $conn->prepare("DELETE FROM support_messages WHERE support_id = ?");
    $stmt->bind_param("i", $support_id);
    $stmt->execute();
    $stmt->close();

    // Presmerovanie späť na stránku so správami
    header("Location: admin_support.php");
    exit;
}

// Načítanie správ z databázy
$sql = "SELECT * FROM support_messages";
$result = $conn->query($sql);
$support_messages = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $support_messages[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Zákaznícky Servis</title>
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
            <h1>Zákaznícky Servis a Podpora</h1>
            <p>Správy od zákazníkov, ktoré sa pýtajú na podporu.</p>
        </header>
        <div class="dashboard-cards">
            <?php if (count($support_messages) > 0): ?>
                <?php foreach ($support_messages as $message): ?>
                    <div class="card">
                        <h2><?php echo htmlspecialchars($message['support_name']); ?> (<?php echo htmlspecialchars($message['email']); ?>)</h2> <!-- Upravené na support_name -->
                        <p><strong>Správa:</strong> <?php echo htmlspecialchars($message['message']); ?></p>
                        <a href="admin_support.php?delete=<?php echo $message['support_id']; ?>" class="cta-button-small">Odstrániť</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Žiadne správy.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
