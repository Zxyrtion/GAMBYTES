<?php
session_start();
require_once 'config/db.php';

// require login
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// if staff/admin, send to admin dashboard
if (in_array($_SESSION['role'] ?? '', ['staff','admin'], true)) {
    header('Location: Dashboard.php');
    exit;
}

// build display name (username preferred)
$displayName = $_SESSION['username'] ?? trim(
    ($_SESSION['user_firstname'] ?? '') . ' ' .
    (trim($_SESSION['user_middlename'] ?? '') ? ($_SESSION['user_middlename'] . ' ') : '') .
    ($_SESSION['user_lastname'] ?? '')
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>User Dashboard | Gambytes</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark fixed-top bg-dark">
    <div class="container">
      <a class="navbar-brand fw-bold text-danger" href="user_dashboard.php">
        <img src="images/LOGO.png" alt="Logo" width="28" height="28" class="me-2 align-middle" />
        Gambytes
      </a>
      <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
        <div class="d-flex align-items-center">
          <span class="text-white me-3 d-none d-md-inline">Welcome, <?php echo htmlspecialchars($displayName); ?></span>
          <div class="dropdown">
            <a href="#" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
              <img src="images/profile.jpg" alt="avatar" class="rounded-circle" width="38" height="38">
            </a>
            <ul class="dropdown-menu dropdown-menu-end profile-menu" aria-labelledby="profileDropdown">
              <li><a class="dropdown-item" href="#">View Profile</a></li>
              <li><a class="dropdown-item" href="logout.php">Logout</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </nav>

  <!-- Sidebar & Main Content -->
  <div class="d-flex" style="margin-top: 70px;">
    <!-- Sidebar -->
    <div class="bg-dark text-white p-4" style="width: 260px; min-height: 100vh;">
      <div class="text-center mb-4">
        <img src="images/profile.jpg" alt="avatar" class="rounded-circle mb-2" width="80" height="80">
        <div class="fw-semibold"><?php echo htmlspecialchars($displayName); ?></div>
      </div>
      <ul class="nav flex-column gap-2">
        <li class="nav-item"><a class="nav-link text-white" href="user_dashboard.php">🏠 Home</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="self_diagnose.php">🩺 Self Diagnose</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="booking.php">📅 Booking</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="booking_list.php">📖 Booking list</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="spendtacker.php">💰 Spending Tracker</a></li>
      </ul>
    </div>

    <!-- Main Content -->
    <div class="flex-grow-1 p-5 bg-light">
      <h2 class="fw-bold text-danger mb-4">Welcome to Your Dashboard</h2>

      <div class="row g-4">
        <div class="col-md-6 col-lg-4">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
              <h5 class="card-title text-danger">🩺 Self Diagnose</h5>
              <p class="card-text text-muted">Quick self-check tools to assess current urges and risk level.</p>
              <a href="self_diagnose.php" class="btn btn-outline-danger btn-sm">Start Diagnose</a>
            </div>
          </div>
        </div>

        <div class="col-md-6 col-lg-4">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
              <h5 class="card-title text-danger">📅 Booking</h5>
              <p class="card-text text-muted">Schedule support sessions or counseling appointments.</p>
              <a href="booking.php" class="btn btn-outline-danger btn-sm">Book Now</a>
            </div>
          </div>
        </div>

        <div class="col-md-6 col-lg-4">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
              <h5 class="card-title text-danger">💰 Spending Tracker</h5>
              <p class="card-text text-muted">Track spending and see trends to support recovery.</p>
              <a href="spendtacker.php" class="btn btn-outline-danger btn-sm">View Tracker</a>
            </div>
          </div>
        </div>
      </div>

      <div class="mt-4">
        <div class="card border-0 shadow-sm bg-light">
          <div class="card-body">
            <h6 class="card-title text-dark mb-2">💪 Daily Motivation</h6>
            <p class="card-text small text-muted mb-0">
              "Recovery is not a race, but a journey. Every small step forward is a victory worth celebrating. 
              You have the strength within you to overcome any challenge that comes your way."
            </p>
          </div>
        </div>
      </div>

      <div class="mt-5">
        <h4 class="fw-bold text-dark mb-3">Recovery Resources</h4>
        <div class="row g-3">
          <div class="col-md-6">
            <div class="card border-0 shadow-sm">
              <div class="card-body">
                <h6 class="card-title text-danger">📚 Educational Content</h6>
                <p class="card-text small text-muted">
                  Access articles, videos, and guides about responsible gambling and recovery strategies.
                </p>
                <a href="#" class="btn btn-outline-danger btn-sm">Browse Resources</a>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card border-0 shadow-sm">
              <div class="card-body">
                <h6 class="card-title text-danger">🤝 Support Community</h6>
                <p class="card-text small text-muted">
                  Connect with others on similar recovery journeys and share experiences in a safe environment.
                </p>
                <a href="#" class="btn btn-outline-danger btn-sm">Join Community</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <footer class="bg-secondary text-white text-center py-3 mt-auto">
    <p class="mb-0 small">© <?php echo date('Y'); ?> Gambytes By: DAVE DELA CERNA | Empowering Responsible Financial Recovery</p>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>