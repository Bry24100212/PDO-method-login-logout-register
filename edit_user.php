<?php
// edit_user.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'auth.php'; // Ensures the user is logged in

$host     = 'localhost';
$dbname   = 'thailand_grocery_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // Check if an ID was passed in the URL
    if (!isset($_GET['id'])) {
        die("No user ID provided.");
    }
    
    $user_id = $_GET['id'];

    // If the form is submitted, update the record
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $email = $_POST['email'];

        $update_sql = "UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email WHERE user_id = :id";
        $stmt = $pdo->prepare($update_sql);
        $stmt->execute([
            ':first_name' => $first_name,
            ':last_name' => $last_name,
            ':email' => $email,
            ':id' => $user_id
        ]);

        // Redirect back to the users table after successful update
        header("Location: users.php");
        exit();
    }

    // Fetch the current user data to pre-fill the form
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = :id");
    $stmt->execute([':id' => $user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        die("User not found.");
    }

} catch (PDOException $e) {
    die("Database Connection Error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background: #f4f6f9; }
        .form-container { background: #fff; padding: 20px; border-radius: 4px; border: 1px solid #ddd; max-width: 400px; margin: 0 auto; }
        input[type="text"], input[type="email"] { width: 100%; padding: 8px; margin: 10px 0 20px; box-sizing: border-box; }
        .btn { padding: 10px 15px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; width: 100%; }
        .btn:hover { background: #0056b3; }
        .cancel { display: block; text-align: center; margin-top: 10px; color: #dc3545; text-decoration: none; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Edit User</h2>
    <form method="POST">
        <label>First Name:</label>
        <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name'], ENT_QUOTES, 'UTF-8') ?>" required>

        <label>Last Name:</label>
        <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name'], ENT_QUOTES, 'UTF-8') ?>" required>

        <label>Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?>" required>

        <button type="submit" class="btn">Update User</button>
        <a href="users.php" class="cancel">Cancel</a>
    </form>
</div>

</body>
</html>
