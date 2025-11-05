<?php
require 'config.php';
if (!isLoggedIn()) header("Location: login.php");
$user = currentUser($pdo);

// Create
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['title'])) {
  $stmt = $pdo->prepare("INSERT INTO tasks (user_id, title, description, due_date, priority) VALUES (?, ?, ?, ?, ?)");
  $stmt->execute([$user['id'], $_POST['title'], $_POST['description'] ?? '', $_POST['due'] ?: null, $_POST['priority'] ?? 'low']);
  header("Location: tasks.php");
  exit;
}

// Toggle complete
if (!empty($_GET['done']) && !empty($_GET['id'])) {
  $id = intval($_GET['id']);
  $pdo->prepare("UPDATE tasks SET status = ? WHERE id = ? AND user_id = ?")
      ->execute(['done', $id, $user['id']]);
  header("Location: tasks.php"); exit;
}

$tasks = $pdo->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY due_date IS NULL, due_date ASC");
$tasks->execute([$user['id']]);
$tasks = $tasks->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
    <head><meta charset="utf-8"><link rel="stylesheet" href="style.css"><title>Tasks</title></head>
<body>
 


<a href="home.php" class="btn back">← Back to Home</a>



<?php include 'header.php'; ?>
<main class="container">
  <h1>Comprehensive Task Management</h1>
  <p class="muted">Organize, prioritize, and conquer your tasks</p>

  <div class="grid-3">
    <div class="card">
      <h3>All Tasks</h3>
      <?php foreach($tasks as $t): ?>
        <div class="list-item">
          <div>
            <input type="checkbox" <?= $t['status']=='done' ? 'checked' : '' ?> disabled>
            <strong><?=htmlspecialchars($t['title'])?></strong>
            <div class="muted">Due: <?=htmlspecialchars($t['due_date'])?> • <?=htmlspecialchars($t['priority'])?></div>
          </div>
          <div class="small-actions">
            <a class="btn small" href="tasks.php?done=1&id=<?=$t['id']?>">Mark Done</a>
            <a class="btn small" href="edit_task.php?id=<?=$t['id']?>">Edit</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="card">
      <h3>Priority View</h3>
      <div class="priority-card high">
        <strong>Final Exam Prep</strong>
        <div class="muted">High priority</div>
      </div>
      <div class="priority-card low">
        <strong>Organize Desk</strong>
        <div class="muted">Low priority</div>
      </div>
    </div>

    <div class="card">
      <h3>Task Details</h3>
      <p>Select a task to view details (view/edit links shown in all tasks list).</p>
    </div>
  </div>

  <div class="center">
    <a href="tasks.php#new" class="btn primary">Add New Task</a>
    <a href="report.php" class="btn">Print Report</a>
  </div>

  <div id="new" class="card">
    <h3>Add Task</h3>
    <form method="post">
      <input name="title" placeholder="Task Title" required>
      <textarea name="description" placeholder="Description"></textarea>
      <input name="due" type="date">
      <select name="priority">
        <option value="low">Low</option><option value="medium">Medium</option><option value="high">High</option>
      </select>
      <button class="btn primary">Add Task</button>
    </form>
  </div>

</main>
</body></html>
