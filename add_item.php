<?php
// add_item.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'auth.php'; // Ensures user is logged in

$host     = 'localhost';
$dbname   = 'thailand_grocery_db';
$username = 'root';
$password = '';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_name        = trim($_POST['item_name'] ?? '');
    $brand_name       = trim($_POST['brand_name'] ?? '');
    $category         = trim($_POST['category'] ?? '');
    $unit_price_thb   = $_POST['unit_price_thb'] ?? 0;
    $net_weight_grams = $_POST['net_weight_grams'] ?? 0;
    $stock_quantity   = $_POST['stock_quantity'] ?? 0;
    $expiration_date  = $_POST['expiration_date'] ?? null;
    
    // Logged-in user performing the addition
    $user_id = $_SESSION['user_id']; 

    if (empty($item_name) || empty($unit_price_thb)) {
        $error = "Please fill in all required fields.";
    } else {
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);

            // Save record with audit tracking (added_by and last_updated_by)
            $sql = "INSERT INTO grocery_items 
                    (item_name, brand_name, category, unit_price_thb, net_weight_grams, stock_quantity, expiration_date, added_by, last_updated_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $item_name, 
                $brand_name, 
                $category, 
                $unit_price_thb, 
                $net_weight_grams, 
                $stock_quantity, 
                $expiration_date, 
                $user_id, 
                $user_id
            ]);

            header('Location: index.php');
            exit();
        } catch (PDOException $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Grocery Item</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; display: flex; justify-content: center; padding: 40px 0; }
        .card { background: #fff; padding: 25px 30px; border-radius: 8px; width: 100%; max-width: 450px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #28a745; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; }
        .alert { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
    </style>
</head>
<body>

<div class="card">
    <h2>Add New Grocery Item</h2>

    <?php if (!empty($error)): ?>
        <div class="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="add_item.php" method="POST">
        <div class="form-group">
            <label>Item Name *</label>
            <input type="text" name="item_name" required>
        </div>
        <div class="form-group">
            <label>Brand Name</label>
            <input type="text" name="brand_name">
        </div>
        <div class="form-group">
            <label>Category</label>
            <input type="text" name="category">
        </div>
        <div class="form-group">
            <label>Unit Price (THB) *</label>
            <input type="number" step="0.01" name="unit_price_thb" required>
        </div>
        <div class="form-group">
            <label>Net Weight (Grams)</label>
            <input type="number" name="net_weight_grams">
        </div>
        <div class="form-group">
            <label>Stock Quantity</label>
            <input type="number" name="stock_quantity">
        </div>
        <div class="form-group">
            <label>Expiration Date</label>
            <input type="date" name="expiration_date">
        </div>
        <button type="submit">Save Item</button>
    </form>
</div>

</body>
</html>
