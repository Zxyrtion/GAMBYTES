<?php
session_start();
require_once 'config/db.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT b.*, s.id AS session_id, s.screening_status
    FROM bookings b
    LEFT JOIN screening_sessions s ON s.booking_id = b.id
    WHERE b.user_id = ?
    ORDER BY b.booking_date DESC, b.booking_time ASC
");
$stmt->execute([$userId]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Book Appointment - Gambytes</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.slot-btn { width: 100%; padding: 10px; margin: 5px 0; border: 1px solid #dee2e6; border-radius: 8px; background: white; }
.slot-btn.selected { background: #0d6efd; color: white; border-color: #0d6efd; }
.slot-btn:hover { transform: translateY(-2px); transition: all 0.2s; }
</style>
</head>
<body>
    <!-- Navbar --> 
   <nav class="navbar navbar-expand-lg navbar-dark fixed-top bg-dark">
    <div class="container">
      <a class="navbar-brand fw-bold text-danger" href="user_dashboard.php">
        <img src="images/LOGO.png" alt="Logo" width="28" height="28" class="me-2 align-middle" />
        Gambytes
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
        <div class="d-flex align-items-center">
            <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a class="nav-link active" href="user_dashboard.php">Home</a></li>
            <a href="#" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
              <img src="profile.jpeg" alt="avatar" class="rounded-circle" width="38" height="38">
            </a>
            <ul class="dropdown-menu dropdown-menu-end profile-menu" aria-labelledby="profileDropdown">
              <li><a class="dropdown-item" href="#">View Profile</a></li>
              <li><a class="dropdown-item" id="logoutBtn" href="logout.php">Logout</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </nav>



<div class="container py-5">
    <h2>My Bookings</h2>

    <table class="table table-striped mt-4">
        <thead>
            <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Full Name</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($bookings as $b): ?>
                <tr>
                    <td><?= htmlspecialchars($b['booking_date']) ?></td>
                    <td><?= htmlspecialchars($b['booking_time']) ?></td>
                    <td><?= htmlspecialchars($b['full_name']) ?></td>
                    <td><?= $b['session_id'] ? htmlspecialchars($b['screening_status']) : 'Not Started' ?></td>
                    <td>
                        <?php if ($b['session_id']): ?>
                            <a href="screening.php?session_id=<?= $b['session_id'] ?>" class="btn btn-sm btn-danger">Open Screening</a>
                        <?php else: ?>
                            <a href="start_screening.php?booking_id=<?= $b['id'] ?>" class="btn btn-sm btn-primary">Start Screening</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>


                                