<?php
require_once 'config.php';

// ✅ Check if user is logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$user = currentUser();
$userName = $user['full_name'] ?? 'User';
$userRole = strtolower($user['role'] ?? 'unknown');

$pdo = dbConnect();

// 🟢 Toggle Done / Not Done
if (isset($_GET['toggle']) && isset($_GET['id'])) {
    $taskId = (int)$_GET['id'];

    $stmt = $pdo->prepare("SELECT is_done FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([$taskId, $user['id']]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($task) {
        if ($task['is_done']) {
            $pdo->prepare("UPDATE tasks SET is_done = 0, completed_at = NULL WHERE id = ? AND user_id = ?")
                ->execute([$taskId, $user['id']]);
        } else {
            $pdo->prepare("UPDATE tasks SET is_done = 1, completed_at = NOW() WHERE id = ? AND user_id = ?")
                ->execute([$taskId, $user['id']]);
        }
    }
    header("Location: task_management.php");
    exit;
}

// 🗑 Delete Task
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?")
        ->execute([(int)$_GET['id'], $user['id']]);
    header("Location: task_management.php");
    exit;
}

// 🗃️ Archive Task
if (isset($_GET['archive']) && isset($_GET['id'])) {
    $taskId = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([$taskId, $user['id']]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($task) {
        $insert = $pdo->prepare("
            INSERT INTO archived_tasks (user_id, title, description, due_date, completed_at)
            VALUES (?, ?, ?, ?, ?)
        ");
        $insert->execute([
            $task['user_id'],
            $task['title'],
            $task['description'],
            $task['due_date'],
            $task['completed_at']
        ]);
        $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?")
            ->execute([$taskId, $user['id']]);
    }
    header("Location: task_management.php");
    exit;
}

// Auto delete done tasks after 3 days
$pdo->prepare("DELETE FROM tasks WHERE is_done = 1 AND completed_at <= DATE_SUB(NOW(), INTERVAL 3 DAY)")->execute();

// Fetch tasks
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY due_date ASC");
$stmt->execute([$user['id']]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Task Management | PlanMyStudy</title>
<link rel="stylesheet" href="style.css">
<style>
/* ===== NAVBAR (same as home) ===== */
header {
  background: #1a2b4a;
  color: white;
  box-shadow: 0 2px 6px rgba(0,0,0,0.2);
  padding: 10px 0;
  position: sticky;
  top: 0;
  z-index: 1000;
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
  transition: 0.2s;
}
nav a:hover, nav a.active {
  color: #1fb14b;
}

/* ===== PAGE BODY ===== */
body {
  background: #ffffff;
  font-family: "Segoe UI", Roboto, Arial, sans-serif;
  margin: 0;
  color: #1a2b4a;
}
.dashboard {
  text-align: center;
  padding: 60px 20px;
  max-width: 1100px;
  margin: auto;
}
.dashboard h2 {
  font-size: 28px;
  color: #1a2b4a;
  margin-bottom: 10px;
}
.dashboard p {
  color: #555;
  font-size: 16px;
  margin-bottom: 30px;
}

/* ===== TASK CARDS ===== */
.task-cards {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 25px;
}
.task-card {
  background: white;
  border-radius: 16px;
  padding: 25px;
  width: 280px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.1);
  transition: transform 0.2s, box-shadow 0.2s;
  text-align: left;
}
.task-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}
.task-card h3 {
  margin: 0 0 10px;
  color: #1a2b4a;
  font-size: 18px;
}
.task-card p {
  font-size: 14px;
  color: #666;
  margin-bottom: 8px;
}
.task-status {
  font-weight: 600;
  margin-bottom: 10px;
}
.status-done {
  color: #1fb14b;
}
.status-pending {
  color: #b32626;
}
.task-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}
.task-actions a {
  background: #1fb14b;
  color: white;
  text-decoration: none;
  padding: 6px 10px;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 600;
  transition: background 0.2s;
}
.task-actions a:hover {
  background: #158c3a;
}
.task-actions a.delete {
  background: #b32626;
}
.task-actions a.delete:hover {
  background: #8a1e1e;
}

/* ===== BUTTONS ===== */
.btn-main {
  background: #1fb14b;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: 0.2s;
}
.btn-main:hover {
  background: #158c3a;
}
</style>
</head>
<body>

<header>
  <div class="nav-container">
    <div class="logo">
      <img src="/image/pms.png" alt="PlanMyStudy Logo">
      <span>PlanMyStudy</span>
    </div>
    <nav>
      <a href="home.php">Home</a>
      <a href="add_task.php">Tasks</a>
         <a href="schedule_tracker.php" class="active">Schedule</a>
      <a href="shared_tasks.php">Shared</a>
      
      
      <a href="reminders.php">Reminders</a>
      <a href="profile.php">Profile</a>
      <a href="logout.php">Logout</a>
    </nav>
  </div>
</header>

<div class="dashboard">
  <h2>Your Tasks</h2>
  <p>Manage your current and upcoming tasks efficiently.</p>

  <div style="text-align:right; margin-bottom:20px;">
    <a href="archived_tasks.php" class="btn-main">🗃 View Archives</a>
    <a href="add_task.php" class="btn-main">➕ Add New Task</a>
  </div>

  <div class="task-cards">
    <?php if (count($tasks) > 0): ?>
      <?php foreach ($tasks as $t): ?>
        <div class="task-card">
          <h3><?= htmlspecialchars($t['title']) ?></h3>
          <p><strong>Due:</strong> <?= htmlspecialchars($t['due_date']) ?></p>
          <p><?= htmlspecialchars($t['description']) ?></p>
          <p class="task-status <?= $t['is_done'] ? 'status-done' : 'status-pending' ?>">
            <?= $t['is_done'] ? '✔ Done' : '⏳ Pending' ?>
          </p>
          <div class="task-actions">
            <a href="edit_task.php?id=<?= $t['id'] ?>">✏ Edit</a>
            <a href="task_management.php?toggle=1&id=<?= $t['id'] ?>">
              <?= $t['is_done'] ? '↩ Unmark' : '✅ Mark Done' ?>
            </a>
            <?php if ($t['is_done']): ?>
              <a href="task_management.php?archive=1&id=<?= $t['id'] ?>">🗃 Archive</a>
            <?php endif; ?>
            <a href="task_management.php?delete=1&id=<?= $t['id'] ?>" class="delete" onclick="return confirm('Delete this task?')">🗑 Delete</a>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p style="text-align:center; color:#555;">No active tasks right now.</p>
    <?php endif; ?>
  </div>

  <br>
  <a href="home.php" class="btn-main">← Back to Dashboard</a>
</div>

</body>
</html>
