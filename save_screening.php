<?php
session_start();
require_once 'config/db.php';

// Check login
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check session_id
if (!isset($_GET['session_id'])) {
    die("No screening session selected.");
}

$session_id = $_GET['session_id'];

// Get submitted answers
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $answers = $_POST['q'] ?? [];

    if (empty($answers)) {
        die("No answers submitted.");
    }

    try {
        // Save each answer into a separate table row (optional: you can save as JSON in one column)
        $stmt = $pdo->prepare("
            UPDATE screening_sessions 
            SET answers = ?, screening_status = 'Completed' 
            WHERE id = ?
        ");

        // Save answers as JSON
        $stmt->execute([json_encode($answers), $session_id]);

        // Redirect to a confirmation page or dashboard
        header("Location: user_dashboard.php?screening=success");
        exit;

    } catch (PDOException $e) {
        die("Failed to save screening: " . $e->getMessage());
    }
} else {
    die("Invalid request method.");
}
