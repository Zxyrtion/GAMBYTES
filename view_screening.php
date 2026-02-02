<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

session_start();
require_once 'config/db.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// only staff/admin allowed
if (!in_array($_SESSION['role'] ?? '', ['staff','admin'])) {
    die('Not authorized.');
}

$sessionId = isset($_GET['session_id']) ? (int)$_GET['session_id'] : 0;
if (!$sessionId) die('Invalid session id.');

// load session + client info
$stmt = $pdo->prepare("
  SELECT s.*, b.full_name, b.email, b.booking_date, b.booking_time
  FROM screening_sessions s
  LEFT JOIN bookings b ON b.id = s.booking_id
  WHERE s.id = ? LIMIT 1
");
$stmt->execute([$sessionId]);
$session = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$session) die('Session not found.');

// same hardcoded questionnaire used by screening.php (ids must match)
$questions = [
  1 => "How often do you gamble?",
  2 => "How much time do you spend gambling on a typical day?",
  3 => "How much time do you spend thinking about gambling on a typical day?",
  4 => "How often have you tried to control, cut down or stop your gambling in the past 12 months?",
  5 => "How often have you gambled to win back money you lost on gambling in the past 12 months?",
  6 => "In the past 12 months, have you gambled more than you planned (longer/more often)?",
  7 => "How often have you lied to others about your gambling in the past 12 months?",
  8 => "How often have you borrowed money or sold something to obtain money for gambling in the past 12 months?",
  9 => "How often have you gambled as a way of escaping problems or relieving negative feelings in the past 12 months?",
  10=> "How often have you gambled with larger sums to get the same feeling of excitement as before, in the past 12 months?",
  11=> "Have you or anyone close to you experienced financial problems due to your gambling?",
  12=> "Has your gambling worsened your mental health?",
  13=> "Have you experienced serious problems in any important relationship because of your gambling?",
  14=> "Have you experienced serious problems at work or school because of your gambling?"
];

// fetch answers (if table not present or no rows, result will be empty)
$answers = [];
try {
    $a = $pdo->prepare("SELECT question_id AS qid, answer_text AS ans, created_at FROM screening_answers WHERE session_id = ?");
    $a->execute([$sessionId]);
    while ($r = $a->fetch(PDO::FETCH_ASSOC)) {
        $answers[(int)$r['qid']] = ['ans' => $r['ans'], 'at' => $r['created_at'] ?? null];
    }
} catch (Exception $e) {
    // ignore, leaves answers empty
}

// simple HTML output
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>View Screening — Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>body{padding:20px;background:#f7f7f8} .card{margin-bottom:12px}</style>
</head>
<body>
<div class="container">
  <h2 class="mb-3">Client Screening Answers</h2>

  <div class="card mb-4">
    <div class="card-body">
      <div><strong>Name:</strong> <?= htmlspecialchars($session['full_name'] ?? '') ?></div>
      <div><strong>Email:</strong> <?= htmlspecialchars($session['email'] ?? '') ?></div>
      <div><strong>Session Status:</strong> <?= htmlspecialchars($session['screening_status'] ?? '') ?></div>
      <div><strong>Started At:</strong> <?= htmlspecialchars($session['created_at'] ?? $session['started_at'] ?? '') ?></div>
    </div>
  </div>

  <?php foreach ($questions as $qid => $text): 
      $row = $answers[$qid] ?? null;
  ?>
    <div class="card">
      <div class="card-body">
        <div class="small text-muted mb-1"><?= $qid ?>.</div>
        <h6><?= htmlspecialchars($text) ?></h6>
        <div class="mt-2"><strong>Answer:</strong></div>
        <div class="mt-1"><?= htmlspecialchars($row['ans'] ?? '') ?: '<span class="text-muted">No answer</span>' ?></div>
        <?php if (!empty($row['at'])): ?>
          <div class="small text-muted mt-2">Answered at: <?= htmlspecialchars($row['at']) ?></div>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>

  <a href="Dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
  <a href="#" class="btn btn-success mt-3">Create Treatment Plan</a>
</div>
</body>
</html>
