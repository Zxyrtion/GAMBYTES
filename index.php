<?php
require_once 'config/db.php';
session_start();

// Get total bookings count (optional)
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM bookings");
    $totalBookings = $stmt->fetchColumn();
} catch(PDOException $e) {
    $totalBookings = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gambytes | Financial Recovery Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css" />
    
</head>
<body class="d-flex flex-column min-vh-100">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold text-danger" href="index.php">Gambytes</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto me-3">
                    <li class="nav-item"><a class="nav-link" href="#home">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                </ul>
                <?php if (!empty($_SESSION['user_id'])): ?>
                    <div class="d-flex align-items-center">
                        <span class="text-white me-3">Welcome, <?php echo htmlspecialchars($_SESSION['user_fullname']); ?></span>
                        <a href="logout.php" class="btn btn-outline-light">Logout</a>
                    </div>
                <?php else: ?>
                    <a href="register.php" class="btn btn-outline-light me-2">Register</a>
                    <a href="login.php" class="btn btn-danger">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-section d-flex align-items-center text-white">
        <div class="container text-center">
            <h6 class="text-uppercase text-danger fw-bold mb-2">Online Gambling Recovery Platform</h6>
          <h1 class="fw-bold display-5">"Where Recovery, meets Responsibility"</h1>
          <p class="lead mb-4">
            Manage your finances, reduce online gambling-related risks, and rebuild financial stability
            through data-driven insights and responsible tools.
          </p>
            <p class="lead mb-4">Professional support for overcoming gambling-related challenges</p>
            <button id="selfDiagHeroBtn" class="btn btn-primary btn-lg me-2" data-bs-toggle="modal" data-bs-target="#selfDiagModal">
                Take Self-Assessment
            </button>
            <a id="bookHeroBtn" href="booking.php" class="btn btn-outline-light btn-lg">Book Consultation</a>
        </div>
    </section>

    <!-- Features -->
  <section id="features" class="py-5 bg-light text-center">
    <div class="container">
      <h2 class="fw-bold text-dark mb-4">Our Core Features</h2>
      <div class="row g-4">
        <div class="col-md-6 col-lg-3">
          <div class="card feature-card border-0 shadow-sm h-100">
            <div class="card-body">
              <h5 class="card-title text-danger">💰 Spending Tracker</h5>
              <p class="card-text text-muted">
                Track all your transactions and identify gambling-related expenses easily.
              </p>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-lg-3">
          <div class="card feature-card border-0 shadow-sm h-100">
            <div class="card-body">
              <h5 class="card-title text-danger">📅 Self-Exclusion Tools</h5>
              <p class="card-text text-muted">
                Set restrictions and manage self-control periods effectively.
              </p>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-lg-3">
          <div class="card feature-card border-0 shadow-sm h-100">
            <div class="card-body">
              <h5 class="card-title text-danger">🧠 Mood Journals</h5>
              <p class="card-text text-muted">
                Record emotional states to identify triggers and progress patterns.
              </p>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-lg-3">
          <div class="card feature-card border-0 shadow-sm h-100">
            <div class="card-body">
              <h5 class="card-title text-danger">👨‍👩‍👧 Family Support</h5>
              <p class="card-text text-muted">
                Enable family involvement to provide emotional and financial accountability.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

    <!-- About Section -->
  <section id="about" class="py-5 bg-dark text-white">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-md-6 mb-4 mb-md-0">
          <img
            src="https://cdn-icons-png.flaticon.com/512/9015/9015136.png"
            alt="About Gambytes"
            class="img-fluid rounded shadow-lg"
          />
        </div>
        <div class="col-md-6">
          <h2 class="fw-bold mb-3 text-danger">About Gambytes</h2>
          <p class="text-light">
            Gambytes is a web and mobile-based solution designed to support individuals struggling
            with online gambling addiction. We aim to help users regain control over their financial
            well-being and promote transparency within families.
          </p>
          <p class="text-light">
            Our mission is to reduce untracked financial lending and gambling-related losses among pilot users.
          </p>
        </div>
      </div>
    </div>
  </section>

    <!-- Footer -->
    <footer class="bg-secondary text-white text-center py-3 mt-auto">
        <p class="mb-0">© <?php echo date('Y'); ?> Gambytes By: DAVE DELA CERNA | Empowering Responsible Financial Recovery</p>
    </footer>

    

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
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-danger">Self-Diagnose</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Register or Login First Modal -->
<div class="modal fade" id="registerFirstModal" tabindex="-1" aria-labelledby="registerFirstLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h5 class="modal-title w-100 text-center" id="registerFirstLabel">Please register or login</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <p>You must register or login first to access this feature.</p>
      </div>
      <div class="modal-footer border-0 justify-content-center">
        <a href="register.php" class="btn btn-primary">Register</a>
        <a href="login.php" class="btn btn-outline-secondary">Login</a>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  const form = document.getElementById('selfDiagForm');
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
    const modalBody = form.querySelector('.modal-body');
    if(modalBody) modalBody.scrollTop = modalBody.scrollHeight;
  });

  const modalEl = document.getElementById('selfDiagModal');
  modalEl.addEventListener('hidden.bs.modal', function () {
    form.reset();
    diagResult.style.display = 'none';
    diagMessage.textContent = '';
    diagMessage.className = 'fw-semibold';
  });
})();
</script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- place this AFTER the bootstrap bundle <script> so bootstrap.Modal is available -->
<script>
  const isLoggedIn = <?php echo !empty($_SESSION['user_id']) ? 'true' : 'false'; ?>;
  document.addEventListener('DOMContentLoaded', () => {
    const selfBtn = document.getElementById('selfDiagHeroBtn');
    const bookBtn = document.getElementById('bookHeroBtn');
    function requireAuth(e){
      if (!isLoggedIn) {
        e.preventDefault();
        const modalEl = document.getElementById('registerFirstModal');
        if (modalEl) {
          const m = new bootstrap.Modal(modalEl);
          m.show();
        } else {
          alert('You must register or login first.');
          window.location.href = 'register.php';
        }
      }
    }
    if (selfBtn) selfBtn.addEventListener('click', requireAuth);
    if (bookBtn) bookBtn.addEventListener('click', requireAuth);
  });
</script>

    <?php
    // Check if coming from successful booking
    if(isset($_GET['booked']) && $_GET['booked'] == 'success'): 
    ?>
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            alert('Thank you for booking! We will contact you shortly.');
        });
    </script>
    <?php endif; ?>
</body>
</html>