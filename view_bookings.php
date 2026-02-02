<?php
session_start();
require_once 'config/db.php';

// Only admins/staff
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin','staff'])) {
    header('Location: login.php');
    exit;
}

// Fetch all bookings with their screening session (if any)
$stmt = $pdo->query("
    SELECT b.*, s.id AS screening_session_id, s.screening_status
    FROM bookings b
    LEFT JOIN screening_sessions s ON s.booking_id = b.id
    ORDER BY b.created_at DESC
");
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>View Bookings - Gambytes</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body { background-color: #f5f6fa; }
    .table thead { background: #212529; color: white; }
    .action-btn { padding: 5px 12px; font-size: 14px; }
</style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container">
      <a class="navbar-brand fw-bold text-danger" href="Dashboard.php">
        <img src="images/LOGO.png" alt="Logo" width="28" height="28" class="me-2 align-middle" />
        Gambytes
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a class="nav-link active" href="Dashboard.php">Home</a></li>
            <li class="nav-item"><a class="nav-link text-white active" href="scheduling.php">Schedule Interview</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="spendtacker.php">Spending Tracker</a></li>
            <li class="nav-item"><a class="nav-link active" href="view_bookings.php">View Bookings</a></li>
            <li class="nav-item"><a class="nav-link active" href="logout.php">Logout</a></li>
        </ul>
      </div>
    </div>
</nav>

<div class="container" style="margin-top: 100px;">
    <div class="card shadow-sm">
        <div class="card-body">
            <h3 class="mb-4 fw-bold">📅 All Bookings</h3>

            <?php if (count($bookings) == 0): ?>
                <div class="alert alert-info">No bookings yet.</div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Notes</th>
                            <th>Screening</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($bookings as $row): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['full_name']) ?></td>
                                <td><?= $row['booking_date'] ?></td>
                                <td><?= date("g:ia", strtotime($row['booking_time'])) ?></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td><?= $row['phone_number'] ?></td>
                                <td><?= $row['notes'] ?></td>

                                <td>
                                    <?php 
                                        // determine if booking is today
                                        $today = date('Y-m-d');
                                        $isToday = ($row['booking_date'] === $today);
                                        $sessionId = $row['screening_session_id'] ?? null;
                                        $status = $row['screening_status'] ?? null;
                                    ?>

                                    <?php if (!empty($sessionId)): ?>
                                        <?php if ($status === 'Completed'): ?>
                                            <a href="view_screening.php?session_id=<?= $sessionId ?>" 
                                               class="btn btn-success btn-sm action-btn">
                                                View Answers
                                            </a>
                                        <?php else: ?>
                                            <!-- session exists but not completed: open screening -->
                                            <a href="screening.php?session_id=<?= $sessionId ?>" 
                                               class="btn btn-danger btn-sm action-btn">
                                                Open Screening
                                            </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <?php if ($isToday): ?>
                                            <!-- only allow start when booking date is today -->
                                            <a href="start_screening.php?booking_id=<?= $row['id'] ?>" 
                                               class="btn btn-primary btn-sm action-btn">
                                                Start Assessment
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Scheduled</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>

                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
