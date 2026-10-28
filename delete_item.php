<?php
// delete_item.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'auth.php'; // Ensures session protection

$host     = 'localhost';
$dbname   = 'thailand_grocery_db';
$username = 'root';
$password = '';

$item_id = $_GET['id'] ?? null;

if ($item_id) {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        // Delete the record from grocery_items
        $stmt = $pdo->prepare("DELETE FROM grocery_items WHERE item_id = ?");
        $stmt->execute([$item_id]);

    } catch (PDOException $e) {
        die("Database Error: " . $e->getMessage());
    }
}

// Redirect back to the dashboard after deletion
header('Location: index.php');
exit();
?>
