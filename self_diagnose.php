<?php
session_start();
require_once 'config/db.php';

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
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Self Diagnose | Gambytes</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css" />
</head>
<body class="d-flex flex-column min-vh-100">
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark fixed-top bg-dark">
    <div class="container">
      <a class="navbar-brand fw-bold text-danger" href="user_dashboard.php">Gambytes</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto me-3">
          <li class="nav-item"><a class="nav-link" href="user_dashboard.php">Home</a></li>
        </ul>
        <?php if (!empty($_SESSION['user_id'])): ?>
          <div class="d-flex align-items-center">
            <span class="text-white me-3">Welcome, <?php echo htmlspecialchars($displayName); ?></span>
            <a href="logout.php" class="btn btn-outline-light">Logout</a>
          </div>
        <?php else: ?>
          <a href="register.php" class="btn btn-outline-light me-2">Register</a>
          <a href="login.php" class="btn btn-danger">Login</a>
        <?php endif; ?>
      </div>
    </div>
  </nav>

  <main class="flex-grow-1" style="margin-top:80px;">
    <div class="container py-5">
      <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
          <div class="card shadow-sm">
            <div class="card-body">
              <h3 class="mb-3 text-danger">Self-Diagnose Checklist</h3>
              <p class="small text-muted">Check each item that applies to you in the past 12 months.</p>

              <form id="selfDiagFormPage" class="needs-validation" novalidate>
                <div class="row g-2">
                  <?php
                  $questions = [
                    "Needs to gamble with increasing amounts of money to achieve the desired excitement.",
                    "Is restless or irritable when attempting to cut down or stop gambling.",
                    "Has made repeated unsuccessful efforts to control, cut back, or stop gambling.",
                    "Is often preoccupied with gambling (thoughts, planning, or reliving experiences).",
                    "Often gambles when feeling distressed (helpless, guilty, anxious, depressed).",
                    "After losing money gambling, often returns another day to get even (\"chasing\").",
                    "Lies to conceal the extent of involvement with gambling.",
                    "Has jeopardized or lost a significant relationship, job, or opportunity because of gambling.",
                    "Relies on others to provide money to relieve desperate financial situations caused by gambling.",
                    "Has made sacrifices (personal, professional, financial) due to gambling."
                  ];
                  foreach ($questions as $i => $q): ?>
                    <div class="col-12">
                      <div class="form-check">
                        <input class="form-check-input diag-checkbox" type="checkbox" value="1" id="q<?php echo $i+1; ?>">
                        <label class="form-check-label" for="q<?php echo $i+1; ?>"><?php echo htmlspecialchars($q); ?></label>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>

                <div id="diagResult" class="mt-3" style="display:none;">
                  <h5>Result</h5>
                  <p id="diagMessage" class="fw-semibold"></p>

                  <div class="alert alert-info mt-2" role="alert">
                    <strong>Note:</strong> This is an informational screening only. For diagnosis and clinical advice consult a professional.
                  </div>

                  <div class="mt-2">
                    <a href="booking.php" class="btn btn-primary" id="bookBtn">Book Appointment</a>
                  </div>
                </div>

                <div class="mt-3 d-flex gap-2">
                  <a href="user_dashboard.php" class="btn btn-secondary">Back</a>
                  <button type="submit" class="btn btn-danger">Self-Diagnose</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <footer class="bg-secondary text-white text-center py-3 mt-auto">
    <p class="mb-0 small">© <?php echo date('Y'); ?> Gambytes By: DAVE DELA CERNA | Empowering Responsible Financial Recovery</p>
  </footer>

  <script>
  (function(){
    const form = document.getElementById('selfDiagFormPage');
    const diagResult = document.getElementById('diagResult');
    const diagMessage = document.getElementById('diagMessage');

    form.addEventListener('submit', function(e){
      e.preventDefault();
      const checked = form.querySelectorAll('.diag-checkbox:checked').length;
      let message = '';
      let cls = '';

      if (checked <= 4) {
        message = `Score: ${checked} — Low / No gambling disorder indicated.`;
        cls = 'text-success';
      } else if (checked <= 7) {
        message = `Score: ${checked} — Moderate risk; consider seeking support.`;
        cls = 'text-warning';
      } else {
        message = `Score: ${checked} — High risk for gambling disorder; please seek professional help.`;
        cls = 'text-danger';
      }

      diagMessage.className = 'fw-semibold ' + cls;
      diagMessage.textContent = message;
      diagResult.style.display = 'block';
      // scroll result into view
      diagResult.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
  })();
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>