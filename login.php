<?php
session_start();
require_once 'config/db.php';

$error = '';
$oldEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $oldEmail = htmlspecialchars($email);

    if ($email === '' || $password === '') {
        $error = 'Please provide email and password.';
    } else {
        // fetch user (no full_name)
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_firstname'] = $user['firstname'];
            $_SESSION['user_middlename'] = $user['middlename'];
            $_SESSION['user_lastname'] = $user['lastname'];
            $_SESSION['role'] = $user['role'];

            // redirect based on role
            if (in_array($user['role'], ['staff','admin'], true)) {
                header('Location: Dashboard.php');
            } else {
                header('Location: user_dashboard.php');
            }
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login | Gambytes</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark fixed-top bg-dark">
    <div class="container">
      <a class="navbar-brand fw-bold text-danger" href="index.php">
        <img src="images/LOGO.png" alt="Logo" width="28" height="28" class="me-2 align-middle" />
        Gambytes
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="index.php#home">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="index.php#features">Features</a></li>
          <li class="nav-item"><a class="nav-link" href="index.php#about">About</a></li>
        </ul>
        <a href="login.php" class="btn btn-danger ms-3">LOGIN</a>
      </div>
    </div>
  </nav>

  <!-- Login Section -->
  <section class="hero-section d-flex align-items-center justify-content-center text-white" style="min-height:100vh;">
    <div class="container" style="max-width:400px; z-index:3;">
      <div class="card bg-dark bg-opacity-75 border-0 shadow-lg">
        <div class="card-body">
          <div class="text-center mb-3">
            <img src="Images/LOGO.png" alt="Logo" width="145" height="150" class="mb-2" />
          </div>
          <h2 class="fw-bold text-danger text-center mb-4">Login to Gambytes</h2>

          <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
          <?php endif; ?>

          <form id="loginForm" method="POST" action="">
            <div class="mb-3">
              <label for="email" class="form-label text-light">Email address</label>
              <input type="email" name="email" class="form-control" id="email" placeholder="Enter email" required value="<?php echo $oldEmail; ?>">
            </div>
            <div class="mb-3">
              <label for="password" class="form-label text-light">Password</label>
              <input type="password" name="password" class="form-control" id="password" placeholder="Enter password" required>
            </div>
            <button type="submit" class="btn btn-danger w-100 fw-bold">Login</button>
          </form>

          <div class="mt-3 text-center">
            <a href="register.php" class="text-danger text-decoration-none">didn't have an account.</a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <footer class="bg-secondary text-white text-center py-3 mt-auto">
    <p class="mb-0 small">© <?php echo date('Y'); ?> Gambytes By: DAVE DELA CERNA | Empowering Responsible Financial Recovery</p>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>