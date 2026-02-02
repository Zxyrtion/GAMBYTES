<?php
session_start();
require_once 'config/db.php';

$error = '';
$success = '';
$old = ['firstname'=>'', 'middlename'=>'', 'lastname'=>'', 'email'=>'', 'role'=>'user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = trim($_POST['firstname'] ?? '');
    $middlename = trim($_POST['middlename'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirmPassword'] ?? '';
    $role = $_POST['role'] ?? 'user';

    $old['firstname'] = htmlspecialchars($firstname);
    $old['middlename'] = htmlspecialchars($middlename);
    $old['lastname'] = htmlspecialchars($lastname);
    $old['email'] = htmlspecialchars($email);
    $old['phone'] = htmlspecialchars($phone);
    $old['role'] = $role;

    if ($firstname === '' || $lastname === '' || $email === '' || $phone === '' || $password === '' || $confirm === '') {
        $error = 'Please fill all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (!in_array($role, ['user','staff'], true)) {
        $error = 'Invalid role selected.';
    } else {
        // check existing email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email is already registered.';
        } else {
            // create username from email local part, ensure uniqueness
            $baseUser = preg_replace('/[^a-z0-9._-]/i', '', strstr($email, '@', true) ?: $email);
            $username = $baseUser ?: 'user'.uniqid();
            $i = 0;
            while (true) {
                $candidate = $username . ($i ? $i : '');
                $check = $pdo->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
                $check->execute([$candidate]);
                if (!$check->fetch()) {
                    $username = $candidate;
                    break;
                }
                $i++;
            }

            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            try {
                $ins = $pdo->prepare("INSERT INTO users (username, firstname, middlename, lastname, email, phone, password_hash, role, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $ins->execute([$username, $firstname ?: null, $middlename ?: null, $lastname ?: null, $email, $phone ?: null, $password_hash, $role]);
                $success = 'Account created successfully. You may <a href="login.php">login</a> now.';
                // clear old inputs
                $old = ['firstname'=>'', 'middlename'=>'', 'lastname'=>'', 'email'=>'', 'phone'=>'', 'role'=>'user'];
            } catch (PDOException $e) {
                $error = 'Failed to create account: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Register | Gambytes</title>
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

  <!-- Register Section -->
  <section class="hero-section d-flex align-items-start justify-content-center text-white position-relative">
    <div class="container" style="z-index:3;">
      <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-5 register-card">
          <div class="card bg-dark bg-opacity-75 border-0 shadow-lg">
             <div class="card-body">
               <h2 class="fw-bold text-danger text-center mb-3">Register</h2>

               <?php if ($error): ?>
                 <div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div>
               <?php endif; ?>

               <?php if ($success): ?>
                 <div class="alert alert-success py-2"><?php echo $success; ?></div>
               <?php endif; ?>

               <form id="registerForm" method="POST" action="">
                 <div class="mb-2">
                   <label for="firstname" class="form-label text-light">First Name</label>
                   <input type="text" class="form-control" id="firstname" name="firstname" placeholder="First name" required value="<?php echo $old['firstname']; ?>">
                 </div>
                 <div class="mb-2">
                   <label for="middlename" class="form-label text-light">Middle Name</label>
                   <input type="text" class="form-control" id="middlename" name="middlename" placeholder="Middle name (optional)" value="<?php echo $old['middlename']; ?>">
                 </div>
                 <div class="mb-2">
                   <label for="lastname" class="form-label text-light">Last Name</label>
                   <input type="text" class="form-control" id="lastname" name="lastname" placeholder="Last name" required value="<?php echo $old['lastname']; ?>">
                 </div>
                 <div class="mb-2">
                   <label for="role" class="form-label text-light">Role</label>
                   <select id="role" name="role" class="form-select" required>
                     <option value="user" <?php echo ($old['role'] === 'user') ? 'selected' : ''; ?>>User</option>
                     <option value="staff" <?php echo ($old['role'] === 'staff') ? 'selected' : ''; ?>>Staff</option>
                   </select>
                 </div>
                 <div class="mb-2">
                   <label for="email" class="form-label text-light">Email address</label>
                   <input type="email" class="form-control" id="email" name="email" placeholder="Enter email" required value="<?php echo $old['email']; ?>">
                 </div>
                 <div class="mb-2">
                   <label for="phone" class="form-label text-light">Phone number</label>
                   <input type="text" class="form-control" id="phone" name="phone" placeholder="Enter phone number" required value="<?php echo $old['phone'] ?? ''; ?>">
                 </div>
                 <div class="mb-2">
                   <label for="password" class="form-label text-light">Password</label>
                   <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
                 </div>
                 <div class="mb-3">
                   <label for="confirmPassword" class="form-label text-light">Confirm Password</label>
                   <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" placeholder="Confirm password" required>
                 </div>
                 <button type="submit" class="btn btn-danger w-100 fw-bold">Register</button>
               </form>
               <div class="mt-2 text-center">
                 <a href="login.php" class="text-danger text-decoration-none small">Already have an account? Login</a>
               </div>
             </div>
           </div>
         </div>
       </div>
    </div>
  </section>

<footer class="bg-secondary text-white text-center py-3 mt-auto">
    <p class="mb-0 small">© <?php echo date('Y'); ?> Gambytes By: DAVE DELA CERNA | Empowering Responsible Financial Recovery</p>





</html></body>  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>  </footer>  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>