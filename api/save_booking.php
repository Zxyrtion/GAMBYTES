<?php
header('Content-Type: application/json');
require_once '../config/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Make sure user is logged in
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $stmt = $pdo->prepare("
        INSERT INTO bookings 
        (booking_date, booking_time, full_name, email, guest_email, phone_number, notes, user_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $data['date'],
        $data['time'],
        $data['fullName'],
        $data['email'],
        $data['guestEmail'] ?? null,
        $data['phoneNumber'],
        $data['notes'] ?? null,
        $_SESSION['user_id']  // <-- Important
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Booking saved successfully'
    ]);

} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to save booking',
        'details' => $e->getMessage()
    ]);
}
