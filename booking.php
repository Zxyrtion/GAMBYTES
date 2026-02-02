<?php
session_start();
require_once 'config/db.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$bookingData = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO bookings 
            (booking_date, booking_time, full_name, email, guest_email, phone_number, notes, user_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $_POST['booking_date'],
            $_POST['booking_time'],
            $_POST['full_name'],
            $_POST['email'],
            !empty($_POST['guest_email']) ? $_POST['guest_email'] : null,
            $_POST['phone_number'],
            !empty($_POST['notes']) ? $_POST['notes'] : null,
            $_SESSION['user_id']
        ]);

        $bookingData = [
            'booking_date' => $_POST['booking_date'],
            'booking_time' => $_POST['booking_time'],
            'full_name' => $_POST['full_name'],
            'email' => $_POST['email'],
            'guest_email' => $_POST['guest_email'] ?? '-',
            'phone_number' => $_POST['phone_number'],
            'notes' => $_POST['notes'] ?? '-'
        ];

    } catch(PDOException $e) {
        $error = "Error creating booking: " . $e->getMessage();
    }
}
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
        <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a class="nav-link active" href="user_dashboard.php">Home</a></li>
        </ul>
        <div class="d-flex align-items-center">
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

  <hr>

<div class="container py-5">
    <h2 class="fw-bold text-danger mb-4">Book Appointment</h2>

    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST" id="bookingForm">
        <!-- Part 1: Date & Time -->
        <div class="row">
            <div class="col-md-5">
                <label class="form-label">Choose date</label>
                <input type="date" name="booking_date" class="form-control" required min="<?= date('Y-m-d') ?>">
                <small class="text-muted d-block mt-2">Available times shown on the right.</small>
            </div>
            <div class="col-md-7">
                <div class="time-slots">
                    <?php
                    $times = ['11:00','11:30','12:00','12:30','13:00','13:30','14:00','14:30','15:00','15:30','16:00'];
                    foreach ($times as $time): ?>
                        <button type="button" class="slot-btn" onclick="selectTime(this, '<?= $time ?>')">
                            <?= date('g:ia', strtotime($time)) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <input type="hidden" name="booking_time" id="selected_time">

        <!-- Part 2: User Details -->
        <div id="details_form" class="mt-4" style="display:none;">
            <h4>Enter Details</h4>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="full_name" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Guest Email (Optional)</label>
                    <input type="email" name="guest_email" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone Number *</label>
                    <input type="tel" name="phone_number" class="form-control" required>
                </div>
                <div class="col-12">
                    <label class="form-label">How can we help you?</label>
                    <textarea name="notes" class="form-control" rows="3"></textarea>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <button type="button" id="nextBtn" class="btn btn-primary" onclick="showDetails()" style="display:none;">Next</button>
            <button type="submit" id="submitBtn" class="btn btn-success" style="display:none;">Schedule Event</button>
            <a href="user_dashboard.php" class="btn btn-outline-secondary">Back</a>
        </div>
    </form>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="successModalLabel">Booking Successfully Created</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <?php if($bookingData): ?>
        <ul class="list-group">
            <li class="list-group-item"><strong>Date:</strong> <?= htmlspecialchars($bookingData['booking_date']) ?></li>
            <li class="list-group-item"><strong>Time:</strong> <?= htmlspecialchars($bookingData['booking_time']) ?></li>
            <li class="list-group-item"><strong>Full Name:</strong> <?= htmlspecialchars($bookingData['full_name']) ?></li>
            <li class="list-group-item"><strong>Email:</strong> <?= htmlspecialchars($bookingData['email']) ?></li>
            <li class="list-group-item"><strong>Guest Email:</strong> <?= htmlspecialchars($bookingData['guest_email']) ?></li>
            <li class="list-group-item"><strong>Phone Number:</strong> <?= htmlspecialchars($bookingData['phone_number']) ?></li>
            <li class="list-group-item"><strong>Notes:</strong> <?= htmlspecialchars($bookingData['notes']) ?></li>
        </ul>
        <?php endif; ?>
      </div>
      <div class="modal-footer">
        <a href="booking_list.php" class="btn btn-primary">View My Bookings</a>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function selectTime(btn, time) {
    document.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
    document.getElementById('selected_time').value = time;
    document.getElementById('nextBtn').style.display = 'inline-block';
}

function showDetails() {
    document.getElementById('details_form').style.display = 'block';
    document.getElementById('nextBtn').style.display = 'none';
    document.getElementById('submitBtn').style.display = 'inline-block';
    document.getElementById('details_form').scrollIntoView({ behavior: 'smooth' });
}

// Show modal if booking was created
<?php if($bookingData): ?>
    var successModal = new bootstrap.Modal(document.getElementById('successModal'));
    successModal.show();
<?php endif; ?>
</script>
</body>
</html>
