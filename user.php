<?php
// users.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'auth.php'; // Ensures the user is logged in

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

    // Base SQL query for the parent entity (users)
    $sql = "SELECT * FROM users";

    // If a search query is submitted, filter by first name, last name, or email
    if (!empty($search_query)) {
        $sql .= " WHERE first_name LIKE :search OR last_name LIKE :search OR email LIKE :search";
    }

    $stmt = $pdo->prepare($sql);

    // Bind the search parameter if searching
    if (!empty($search_query)) {
        $stmt->bindValue(':search', '%' . $search_query . '%');
    }

    $stmt->execute();
    $users = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Database Connection Error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users - Thailand Grocery</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background: #f4f6f9; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .nav-links a { margin-right: 15px; text-decoration: none; color: #007bff; font-weight: bold; }
        .nav-links a:hover { text-decoration: underline; }
        .search-container { margin-bottom: 20px; background: #fff; padding: 15px; border-radius: 4px; border: 1px solid #ddd; }
        .search-input { padding: 8px; width: 300px; border: 1px solid #ccc; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; background: #fff; }
        th, td { padding: 10px 12px; border: 1px solid #ddd; text-align: left; }
        th { background: #007bff; color: white; }
        .btn { padding: 8px 12px; background: #28a745; color: white; text-decoration: none; border-radius: 4px; border: none; cursor: pointer; }
        .btn-primary { background: #007bff; }
        .btn-logout { background: #dc3545; }
        .btn-edit { background: #ffc107; color: #000; padding: 4px 8px; border-radius: 4px; text-decoration: none; font-size: 14px; margin-right: 5px; }
    </style>
</head>
<body>

<div class="header">
    <h2>Manage Users (Parent Entity)</h2>
    <div>
        <span class="nav-links">
            <a href="index.php">Back to Dashboard</a>
        </span>
        <a href="logout.php" class="btn btn-logout">Logout</a>
    </div>
</div>

<!-- SEARCH BAR SECTION -->
<div class="search-container">
    <form method="GET" action="users.php">
        <input type="text" name="search" class="search-input" placeholder="Search by name or email..." value="<?= htmlspecialchars($search_query, ENT_QUOTES, 'UTF-8') ?>">
        <button type="submit" class="btn btn-primary">Search</button>
        <a href="users.php" class="btn" style="background:#6c757d;">Clear</a>
    </form>
</div>

<h3>Registered Users Directory</h3>
<table>
    <thead>
        <tr>
            <th>User ID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Email</th>
            <th>Date Added</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($users)): ?>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?= htmlspecialchars($user['user_id'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($user['first_name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($user['last_name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($user['date_added'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <!-- Link to an edit_user.php page which you will need for Part I - C -->
                    <a href="edit_user.php?id=<?= htmlspecialchars($user['user_id'], ENT_QUOTES, 'UTF-8') ?>" class="btn-edit">Edit</a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" style="text-align: center;">No users found matching your search.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>
