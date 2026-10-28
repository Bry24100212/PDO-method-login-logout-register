<?php
// index.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'auth.php'; // Ensures user is logged in

$host     = 'localhost';
$dbname   = 'thailand_grocery_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // Fetch items along with added_by and last_updated_by user names
    $sql = "SELECT 
                g.*,
                CONCAT(u1.first_name, ' ', u1.last_name) AS creator_name,
                CONCAT(u2.first_name, ' ', u2.last_name) AS updater_name
            FROM grocery_items g
            LEFT JOIN users u1 ON g.added_by = u1.user_id
            LEFT JOIN users u2 ON g.last_updated_by = u2.user_id";

    $stmt = $pdo->query($sql);
    $items = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
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
        table { width: 100%; border-collapse: collapse; background: #fff; }
        th, td { padding: 10px 12px; border: 1px solid #ddd; text-align: left; }
        th { background: #007bff; color: white; }
        .btn { padding: 8px 12px; background: #28a745; color: white; text-decoration: none; border-radius: 4px; }
        .btn-logout { background: #dc3545; }
        .btn-edit { background: #ffc107; color: #000; padding: 4px 8px; border-radius: 4px; text-decoration: none; font-size: 14px; margin-right: 5px; }
        .btn-delete { background: #dc3545; color: white; padding: 4px 8px; border-radius: 4px; text-decoration: none; font-size: 14px; }
        .btn-delete:hover { background: #bd2130; }
    </style>
</head>
<body>

<div class="header">
    <h2>Welcome, <?= htmlspecialchars($_SESSION['first_name'] ?? 'User') ?>!</h2>
    <div>
        <a href="add_item.php" class="btn">Add Grocery Item</a>
        <a href="logout.php" class="btn btn-logout">Logout</a>
    </div>
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
            <?php foreach ($items as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['item_id']) ?></td>
                <td><?= htmlspecialchars($item['item_name']) ?></td>
                <td><?= htmlspecialchars($item['unit_price_thb'] ?? $item['price'] ?? '0.00') ?></td>
                <td><?= htmlspecialchars($item['creator_name'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($item['updater_name'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($item['last_updated'] ?? 'N/A') ?></td>
                <td>
                    <a href="edit_item.php?id=<?= $item['item_id'] ?>" class="btn-edit">Edit</a>
                    <a href="delete_item.php?id=<?= $item['item_id'] ?>" 
                       class="btn-delete" 
                       onclick="return confirm('Are you sure you want to delete this item?');">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" style="text-align: center;">No grocery items found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>
