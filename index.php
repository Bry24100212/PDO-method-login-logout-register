<?php
// index.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'auth.php'; // Ensures user is logged in

header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline';");

$host     = 'localhost';
$dbname   = 'thailand_grocery_db';
$username = 'root';
$password = '';

// Capture the search query if it exists
$search_query = $_GET['search'] ?? '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // Base SQL query
    $sql = "SELECT 
                g.*,
                CONCAT(u1.first_name, ' ', u1.last_name) AS creator_name,
                CONCAT(u2.first_name, ' ', u2.last_name) AS updater_name
            FROM grocery_items g
            LEFT JOIN users u1 ON g.added_by = u1.user_id
            LEFT JOIN users u2 ON g.last_updated_by = u2.user_id";

    // If a search query is submitted, filter the results by item_name
    if (!empty($search_query)) {
        $sql .= " WHERE g.item_name LIKE :search";
    }

    $stmt = $pdo->prepare($sql);

    // Bind the search parameter if searching
    if (!empty($search_query)) {
        $stmt->bindValue(':search', '%' . $search_query . '%');
    }

    $stmt->execute();
    $items = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Database Connection Error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Thailand Grocery - Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background: #f4f6f9; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .search-container { margin-bottom: 20px; background: #fff; padding: 15px; border-radius: 4px; border: 1px solid #ddd; }
        .search-input { padding: 8px; width: 300px; border: 1px solid #ccc; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; background: #fff; }
        th, td { padding: 10px 12px; border: 1px solid #ddd; text-align: left; }
        th { background: #007bff; color: white; }
        .btn { padding: 8px 12px; background: #28a745; color: white; text-decoration: none; border-radius: 4px; border: none; cursor: pointer; }
        .btn-logout { background: #dc3545; }
        .btn-primary { background: #007bff; }
        .btn-edit { background: #ffc107; color: #000; padding: 4px 8px; border-radius: 4px; text-decoration: none; font-size: 14px; margin-right: 5px; }
        .btn-delete { background: #dc3545; color: white; padding: 4px 8px; border-radius: 4px; text-decoration: none; font-size: 14px; }
        .btn-delete:hover { background: #bd2130; }
    </style>
</head>
<body>

<div class="header">
    <h2>Welcome, <?= htmlspecialchars($_SESSION['first_name'] ?? 'User', ENT_QUOTES, 'UTF-8') ?>!</h2>
    <div>
        <a href="add_item.php" class="btn">Add Grocery Item</a>
        <a href="logout.php" class="btn btn-logout">Logout</a>
    </div>
</div>

<!-- SEARCH BAR SECTION -->
<div class="search-container">
    <form method="GET" action="index.php">
        <input type="text" name="search" class="search-input" placeholder="Search for a grocery item..." value="<?= htmlspecialchars($search_query, ENT_QUOTES, 'UTF-8') ?>">
        <button type="submit" class="btn btn-primary">Search</button>
        <a href="index.php" class="btn" style="background:#6c757d;">Clear</a>
    </form>
</div>

<h3>Grocery Items Directory</h3>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Item Name</th>
            <th>Price (THB)</th>
            <th>Added By</th>
            <th>Last Updated By</th>
            <th>Last Updated At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($items)): ?>
            <?php $display_id = 1; ?>
            <?php foreach ($items as $item): ?>
            <tr>
                <td><?= $display_id++; ?></td>
                <td><?= htmlspecialchars($item['item_name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($item['unit_price_thb'] ?? $item['price'] ?? '0.00', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($item['creator_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($item['updater_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($item['last_updated'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <a href="edit_item.php?id=<?= htmlspecialchars($item['item_id'], ENT_QUOTES, 'UTF-8') ?>" class="btn-edit">Edit</a>
                    <a href="delete_item.php?id=<?= htmlspecialchars($item['item_id'], ENT_QUOTES, 'UTF-8') ?>" 
                       class="btn-delete" 
                       onclick="return confirm('Are you sure you want to delete this item?');">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" style="text-align: center;">No grocery items found matching your search.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>
