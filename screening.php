<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

session_start();
require_once 'config/db.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$sessionId = isset($_GET['session_id']) ? (int)$_GET['session_id'] : 0;
if (!$sessionId) die('Invalid screening session.');

// load session + booking/client info
$stmt = $pdo->prepare("
  SELECT s.*, b.full_name, b.email, b.booking_date, b.booking_time
  FROM screening_sessions s
  LEFT JOIN bookings b ON b.id = s.booking_id
  WHERE s.id = ? LIMIT 1
");
$stmt->execute([$sessionId]);
$session = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$session) die('Screening session not found.');

$isClient = ($_SESSION['user_id'] == $session['client_id']);
$isStaff  = in_array($_SESSION['role'] ?? '', ['staff','admin']);
if (!$isClient && !$isStaff) die('Not authorized.');

// Hardcoded questionnaire (matches your image)
$questions = [
  1 => ['text'=>"How often do you gamble?", 'type'=>'freq'],
  2 => ['text'=>"How much time do you spend gambling on a typical day?", 'type'=>'freq'],
  3 => ['text'=>"How much time do you spend thinking about gambling on a typical day?", 'type'=>'freq'],
  4 => ['text'=>"How often have you tried to control, cut down or stop your gambling in the past 12 months?", 'type'=>'freq'],
  5 => ['text'=>"How often have you gambled to win back money you lost on gambling in the past 12 months?", 'type'=>'freq'],
  6 => ['text'=>"In the past 12 months, have you gambled more than you planned (longer/more often)?", 'type'=>'freq'],
  7 => ['text'=>"How often have you lied to others about your gambling in the past 12 months?", 'type'=>'freq'],
  8 => ['text'=>"How often have you borrowed money or sold something to obtain money for gambling in the past 12 months?", 'type'=>'freq'],
  9 => ['text'=>"How often have you gambled as a way of escaping problems or relieving negative feelings in the past 12 months?", 'type'=>'freq'],
  10=> ['text'=>"How often have you gambled with larger sums to get the same feeling of excitement as before, in the past 12 months?", 'type'=>'freq'],
  11=> ['text'=>"Have you or anyone close to you experienced financial problems due to your gambling?", 'type'=>'yespast'],
  12=> ['text'=>"Has your gambling worsened your mental health?", 'type'=>'yespast'],
  13=> ['text'=>"Have you experienced serious problems in any important relationship because of your gambling?", 'type'=>'yespast'],
  14=> ['text'=>"Have you experienced serious problems at work or school because of your gambling?", 'type'=>'yespast'],
];

// option sets
$opts_freq = ['Never','Monthly or less','2-4 times a month','2-3 times a week','4 or more times a week','Daily','Several times a day'];
$opts_yespast = ['No','Yes, but not in the past year','Yes, in the past year'];

// Ensure screening_answers table exists (simple schema)
try {
    $pdo->exec("
      CREATE TABLE IF NOT EXISTS screening_answers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        session_id INT NOT NULL,
        question_id INT NOT NULL,
        answer_text TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        created_by INT DEFAULT NULL,
        INDEX(session_id)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
} catch (Exception $e) {
    // friendly fallback: we'll still try to read if table missing
}

// load existing answers (if any)
$existingAnswers = [];
try {
    $sel = $pdo->prepare("SELECT question_id AS qid, answer_text AS ans FROM screening_answers WHERE session_id = ?");
    $sel->execute([$sessionId]);
    while ($r = $sel->fetch(PDO::FETCH_ASSOC)) {
        $existingAnswers[(int)$r['qid']] = $r['ans'];
    }
} catch (Exception $e) {
    // ignore
}

$error = '';
$completed = false;

// handle POST (only client should submit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isClient) {
    $answers = $_POST['answers'] ?? [];
    try {
        $pdo->beginTransaction();

        // delete previous answers for this session
        $del = $pdo->prepare("DELETE FROM screening_answers WHERE session_id = ?");
        $del->execute([$sessionId]);

        // insert answers
        $ins = $pdo->prepare("INSERT INTO screening_answers (session_id, question_id, answer_text, created_by) VALUES (?, ?, ?, ?)");
        foreach ($questions as $qid => $q) {
            $val = $answers[$qid] ?? '';
            if (is_array($val)) $val = implode('|', $val);
            $ins->execute([$sessionId, $qid, trim($val), $_SESSION['user_id']]);
        }

        // update screening_sessions status (set completed if column exists)
        $check = $pdo->prepare("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='screening_sessions' AND COLUMN_NAME='completed_at' LIMIT 1");
        $check->execute();
        if ($check->fetchColumn()) {
            $upd = $pdo->prepare("UPDATE screening_sessions SET screening_status='Completed', completed_at=NOW() WHERE id = ?");
        } else {
            $upd = $pdo->prepare("UPDATE screening_sessions SET screening_status='Completed' WHERE id = ?");
        }
        $upd->execute([$sessionId]);

        $pdo->commit();
        $completed = true;

        // refresh existingAnswers for display
        foreach ($questions as $qid => $_) {
            $existingAnswers[$qid] = $answers[$qid] ?? '';
            if (is_array($existingAnswers[$qid])) $existingAnswers[$qid] = implode('|', $existingAnswers[$qid]);
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $error = 'Failed to save answers: ' . $e->getMessage();
    }
}
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Screening — Gambytes</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.navbar{padding:.35rem 1rem;min-height:48px} body{padding-top:60px}
.container{max-width:980px}
.card.question{margin-bottom:12px}
</style>
</head>
<body>
<nav class="navbar navbar-dark bg-dark fixed-top">
  <div class="container">
    <a class="navbar-brand text-danger" href="#">Gambytes</a>
    <div class="text-white ms-auto"><?= htmlspecialchars(($session['full_name'] ?? '') . ' — ' . ($session['booking_date'] ?? '') . ' ' . ($session['booking_time'] ?? '')) ?></div>
  </div>
</nav>

<div class="container mt-3">
  <h3><?= $isClient ? 'Complete Your Assessment' : 'Screening (Read Only)' ?></h3>

  <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <?php if ($completed): ?>
    <div class="alert alert-success">Answers saved. Your case manager will review them.</div>
  <?php endif; ?>

  <form method="post">
    <?php foreach ($questions as $qid => $q): 
      $existing = $existingAnswers[$qid] ?? '';
      $type = $q['type'];
    ?>
      <div class="card question">
        <div class="card-body">
          <div><strong><?= $qid ?>. <?= htmlspecialchars($q['text']) ?></strong></div>
          <div class="mt-2">
            <?php
              $opts = ($type === 'freq') ? $opts_freq : $opts_yespast;
              foreach ($opts as $opt):
            ?>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio"
                       name="answers[<?= $qid ?>]" id="q<?= $qid ?>_<?= md5($opt) ?>"
                       value="<?= htmlspecialchars($opt) ?>"
                       <?= ($existing === $opt) ? 'checked' : '' ?>
                       <?= $isClient ? '' : 'disabled' ?>>
                <label class="form-check-label" for="q<?= $qid ?>_<?= md5($opt) ?>"><?= htmlspecialchars($opt) ?></label>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>

    <div class="mb-5">
      <?php if ($isClient): ?><button class="btn btn-success">Submit Assessment</button><?php endif; ?>
      <a class="btn btn-secondary" href="<?= $isStaff ? 'view_bookings.php' : 'user_dashboard.php' ?>">Back</a>
    </div>
  </form>
</div>

<!-- completion modal (show when $completed is true) -->
<div class="modal fade" id="completeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0">
        <div class="mx-auto rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width:56px;height:56px;">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-check" viewBox="0 0 16 16">
            <path d="M13.485 1.929a.75.75 0 0 1 0 1.06L6.984 9.49a.75.75 0 0 1-1.06 0L2.515 6.08a.75.75 0 1 1 1.06-1.06L6 8.444l6.485-6.515a.75.75 0 0 1 1.06 0z"/>
          </svg>
        </div>
      </div>
      <div class="modal-body text-center">
        <h5 class="mb-1">Checked In with Case Manager</h5>
        <p class="text-muted mb-3">You have been assigned a case manager</p>

        <div class="card text-start mb-3">
          <div class="card-body p-3">
            <div class="small text-uppercase text-muted">Status</div>
            <div class="mb-2">Active Assessment</div>
            <hr class="my-2">
            <div class="small text-uppercase text-muted">Next step</div>
            <div class="mb-2">Review Your Assessment Results</div>
            <hr class="my-2">
            <div class="small text-uppercase text-muted">Note</div>
            <div class="small text-muted">Your case manager will review your assessment and contact you shortly with personalized recommendations.</div>
          </div>
        </div>

        <a href="view_screening.php?session_id=<?= $sessionId ?>" class="btn btn-dark w-100 mb-2">View Results</a>
        <button type="button" id="completeCloseBtn" class="btn btn-outline-secondary w-100" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<?php $backUrl = $isStaff ? 'view_bookings.php' : 'user_dashboard.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
<?php if ($completed): ?>
  var completeModal = new bootstrap.Modal(document.getElementById('completeModal'));
  completeModal.show();

  // redirect to dashboard when modal is closed
  document.getElementById('completeModal').addEventListener('hidden.bs.modal', function () {
    window.location.href = '<?= $backUrl ?>';
  });

  // also handle explicit Close button click (defensive)
  var closeBtn = document.getElementById('completeCloseBtn');
  if (closeBtn) {
    closeBtn.addEventListener('click', function () {
      window.location.href = '<?= $backUrl ?>';
    });
  }
<?php endif; ?>
</script>
</body>
</html>
