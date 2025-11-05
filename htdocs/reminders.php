<?php
require_once 'config.php';
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$user = currentUser();
$pdo = dbConnect();

$stmt = $pdo->prepare("
    SELECT r.*, t.title 
    FROM reminders r 
    JOIN tasks t ON r.task_id = t.id 
    WHERE r.user_id = ? 
    ORDER BY r.remind_date ASC
");
$stmt->execute([$user['id']]);
$reminders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reminders | PlanMyStudy</title>
<style>
/* Navbar */
header {
  background: #1a2b4a;
  color: white;
  box-shadow: 0 2px 6px rgba(0,0,0,0.2);
  padding: 10px 0;
}
.nav-container {
  width: 90%;
  max-width: 1100px;
  margin: auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.logo {
  display: flex;
  align-items: center;
  gap: 10px;
}
.logo img {
  width: 40px;
  height: 40px;
  border-radius: 50%;
}
.logo span {
  font-weight: 700;
  font-size: 20px;
  color: white;
}
nav a {
  text-decoration: none;
  color: white;
  font-weight: 500;
  margin-left: 18px;
}
nav a:hover, nav a.active { color: #1fb14b; }

/* Body */
body {
  background: #ffffff;
  font-family: "Segoe UI", Roboto, Arial, sans-serif;
  margin: 0;
  color: #1a2b4a;
}
.container {
  background: white;
  border-radius: 16px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.1);
  width: 90%;
  max-width: 900px;
  margin: 60px auto;
  padding: 40px;
}
h1 {
  text-align: center;
  margin-bottom: 25px;
  color: #1a2b4a;
}

/* Reminder cards */
.reminder {
  background: #fdfdfd;
  border: 1px solid #e0e0e0;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 18px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}
.reminder strong {
  font-size: 17px;
  color: #1a2b4a;
}
.reminder small {
  color: #666;
  font-size: 13px;
}
.reminder p {
  color: #333;
  margin: 8px 0;
  font-size: 15px;
}

/* Buttons */
.btn {
  background: #1fb14b;
  color: white;
  border: none;
  border-radius: 8px;
  padding: 10px 18px;
  cursor: pointer;
  font-weight: 600;
  text-decoration: none;
}
.btn:hover {
  background: #158c3a;
}

/* Footer link */
.back {
  text-align: center;
  margin-top: 25px;
}
.back a {
  color: #1fb14b;
  text-decoration: none;
}
.back a:hover {
  text-decoration: underline;
}
</style>
</head>
<body>

<header>
  <div class="nav-container">
    <div class="logo">
      <img src="/image/pms.png" alt="PlanMyStudy Logo" class="pms-logo">

      <span>PlanMyStudy</span>
    </div>
    <nav>
      <a href="home.php">Home</a>
      <a href="add_task.php">Tasks</a>
      <a href="schedule_tracker.php">Schedule</a>
      <a href="shared_tasks.php">Shared</a>
      <a href="reminders.php" class="active">Reminders</a>
      <a href="profile.php">Profile</a>
      <a href="logout.php">Logout</a>
    </nav>
  </div>
</header>

<main class="container">
  <h1>Your Reminders</h1>

  <?php if (empty($reminders)): ?>
    <p style="text-align:center; color:#777;">No reminders set yet.</p>
  <?php else: ?>
    <?php foreach ($reminders as $r): ?>
      <div class="reminder">
        <strong><?= htmlspecialchars($r['title']) ?></strong>
        <p><?= htmlspecialchars($r['message']) ?></p>
        <small>📅 Reminder Date: <?= htmlspecialchars($r['remind_date']) ?></small>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

  <div class="back">
    <a href="home.php">← Back to Dashboard</a>
  </div>
</main>

</body>
</html>
