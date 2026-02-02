<?php
require_once 'config/db.php';
$username = 'admin';
$password = 'password123'; // change this immediately
$full_name = 'Admin User';
$email = 'admin@example.com';

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO users (username, full_name, email, password_hash) VALUES (?, ?, ?, ?)");
try {
    $stmt->execute([$username, $full_name, $email, $hash]);
    echo "User created. Username: $username";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>