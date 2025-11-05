<?php
require 'config.php';
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$user = currentUser($pdo);
$pdo = dbConnect();

if (!isset($_GET['id'])) {
    die("Task not specified.");
}

$taskId = (int)$_GET['id'];

// Fetch task and shared info
$stmt = $pdo->prepare("
    SELECT 
        t.id AS task_id, 
        t.title, 
        t.description, 
        t.due_date, 
        t.priority,
        u1.full_name AS owner_name, 
        u2.full_name AS collaborator_name,
        st.owner_done, 
        st.collaborator_done, 
        st.accepted,
        st.completed_at
    FROM tasks t
    LEFT JOIN shared_tasks st ON t.id = st.task_id
    LEFT JOIN users u1 ON st.owner_id = u1.id
    LEFT JOIN users u2 ON st.collaborator_id = u2.id
    WHERE t.id = ?
    LIMIT 1
");
$stmt->execute([$taskId]);
$task = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$task) {
    die("Task not found or access denied.");
}

$isCompleted = ($task['owner_done'] && $task['collaborator_done']);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>View Task | PlanMyStudy</title>
<style>
body {
  background: #f8fbf9;
  font-family: "Segoe UI", Roboto, Arial, sans-serif;
  margin: 0;
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
}
.container {
  background: white;
  width: 600px;
  border-radius: 12px;
  padding: 40px;
  box-shadow: 0 6px 20px rgba(0,0,0,0.1);
}
h1 {
  color: #1a2b4a;
  margin-bottom: 10px;
  text-align: center;
}
.details {
  margin-top: 20px;
}
.details p {
  margin: 10px 0;
  color: #333;
}
.label {
  font-weight: bold;
  color: #1fb14b;
}
.status {
  font-weight: 600;
  padding: 6px 10px;
  border-radius: 6px;
}
.status.done { color: green; }
.status.pending { color: #b32626; }
.btn {
  background: #1fb14b;
  color: white;
  padding: 10px 18px;
  border: none;
  border-radius: 8px;
  text-decoration: none;
  display: inline-block;
  margin-top: 20px;
  font-size: 15px;
}
.btn:hover { background: #158c3a; }
</style>
</head>
<body>



<div class="container">
  <h1><?= htmlspecialchars($task['title']) ?></h1>
  <div class="details">
    <p><span class="label">Description:</span><br><?= nl2br(htmlspecialchars($task['description'])) ?></p>
    <p><span class="label">Priority:</span> <?= htmlspecialchars(ucfirst($task['priority'])) ?></p>
    <p><span class="label">Due Date:</span> <?= htmlspecialchars($task['due_date']) ?></p>
    <p><span class="label">Owner:</span> <?= htmlspecialchars($task['owner_name'] ?? 'N/A') ?></p>
    <p><span class="label">Collaborator:</span> <?= htmlspecialchars($task['collaborator_name'] ?? 'None') ?></p>
    <p><span class="label">Status:</span> 
      <span class="status <?= $isCompleted ? 'done' : 'pending' ?>">
        <?= $isCompleted ? '✅ Completed by both' : '⏳ Ongoing' ?>
      </span>
    </p>
    <?php if ($task['completed_at']): ?>
      <p><span class="label">Completed At:</span> <?= htmlspecialchars($task['completed_at']) ?></p>
    <?php endif; ?>
  </div>

  <a href="shared_tasks.php" class="btn">← Back to Shared Tasks</a>
</div>
</body>
</html>
