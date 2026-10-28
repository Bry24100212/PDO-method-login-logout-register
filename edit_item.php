<?php
// edit_item.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'auth.php'; // Ensures session protection

$host     = 'localhost';
$dbname   = 'thailand_grocery_db';
$username = 'root';
$password = '';

$error = '';
$item_id = $_GET['id'] ?? $_POST['item_id'] ?? null;

if (!$item_id) {
    header('Location: index.php');
    exit();
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // Handle Form Update Submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $item_name      = trim($_POST['item_name'] ?? '');
        $unit_price_thb = $_POST['unit_price_thb'] ?? 0;
        $user_id        = $_SESSION['user_id']; // Current logged-in user updating the record

        if (empty($item_name)) {
            $error = "Item Name cannot be empty.";
        } else {
            // Update item details and last_updated_by audit field
            $sql = "UPDATE grocery_items 
                    SET item_name = ?, unit_price_thb = ?, last_updated_by = ? 
                    WHERE item_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$item_name, $unit_price_thb, $user_id, $item_id]);

            header('Location: index.php');
            exit();
        }
    }

    // Fetch existing item details to populate form
    $stmt = $pdo->prepare("SELECT * FROM grocery_items WHERE item_id = ?");
    $stmt->execute([$item_id]);
    $item = $stmt->fetch();

    if (!$item) {
        die("Item not found.");
    }
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Grocery Item</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; display: flex; justify-content: center; padding: 40px 0; }
        .card { background: #fff; padding: 25px 30px; border-radius: 8px; width: 100%; max-width: 400px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #ffc107; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; }
        .cancel-btn { display: block; text-align: center; margin-top: 10px; color: #6c757d; text-decoration: none; }
    </style>
</head>
<body>

<div class="card">
    <h2>Edit Grocery Item</h2>

    <?php if (!empty($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form action="edit_item.php" method="POST">
        <input type="hidden" name="item_id" value="<?= htmlspecialchars($item['item_id']) ?>">

        <div class="form-group">
            <label>Item Name</label>
            <input type="text" name="item_name" value="<?= htmlspecialchars($item['item_name']) ?>" required>
        </div>

        <div class="form-group">
            <label>Unit Price (THB)</label>
            <input type="number" step="0.01" name="unit_price_thb" value="<?= htmlspecialchars($item['unit_price_thb'] ?? $item['price'] ?? 0) ?>" required>
        </div>

        <button type="submit">Update Item</button>
        <a href="index.php" class="cancel-btn">Cancel</a>
    </form>
</div>

</body>
</html>
