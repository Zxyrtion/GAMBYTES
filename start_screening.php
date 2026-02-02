<?php
session_start();
require_once 'config/db.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['booking_id'])) {
    die("Invalid request");
}

$bookingId = (int) $_GET['booking_id'];

// Check if session already exists
$stmt = $pdo->prepare("SELECT * FROM screening_sessions WHERE booking_id = ?");
$stmt->execute([$bookingId]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    header("Location: screening.php?session_id=" . $existing['id']);
    exit;
}

try {
    // get booking owner (user_id)
    $bStmt = $pdo->prepare("SELECT user_id FROM bookings WHERE id = ?");
    $bStmt->execute([$bookingId]);
    $booking = $bStmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        throw new Exception("Booking not found.");
    }

    $clientId = $booking['user_id'];

    $ins = $pdo->prepare("
        INSERT INTO screening_sessions (booking_id, client_id, screening_status, created_at)
        VALUES (?, ?, 'In Progress', NOW())
    ");
    $ins->execute([$bookingId, $clientId]);

    $session_id = $pdo->lastInsertId();

    header("Location: screening.php?session_id=" . $session_id);
    exit;
} catch (Exception $e) {
    die("Failed to start screening: " . $e->getMessage());
}
?>
