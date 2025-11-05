<?php
require_once 'config.php';
$user = currentUser();
if (!$user) {
    header("Location: login.php");
    exit;
}

$pdo = dbConnect();

// Get user's archived tasks
$stmt = $pdo->prepare("SELECT * FROM archived_tasks WHERE user_id = ? ORDER BY archived_at DESC");
$stmt->execute([$user['id']]);
$archives = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Archived Tasks | PlanMyStudy</title>
<style>
body {
  background: #f8fbf9;
  font-family: "Segoe UI", Roboto, Arial, sans-serif;
  margin: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 40px;
}
.container {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.1);
  padding: 40px;
  width: 90%;
  max-width: 800px;
}
h1 {
  text-align: center;
  color: #1a2b4a;
}
table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}
th, td {
  border: 1px solid #e0e0e0;
  padding: 12px;
  text-align: left;
}
th {
  background: #1fb14b;
  color: white;
}
tr:nth-child(even) {
  background: #f4f9f5;
}
.back-link, .btn {
  display: inline-block;
  margin-top: 20px;
  text-decoration: none;
  color: white;
  background: #1fb14b;
  padding: 10px 20px;
  border-radius: 6px;
}
.back-link:hover, .btn:hover {
  background: #158c3a;
}
.empty {
  text-align: center;
  color: #777;
}
</style>
</head>
<body>
  

<div class="container">
  <h1>Archived Tasks</h1>

  <?php if (count($archives) > 0): ?>
    <table>
      <thead>
        <tr>
          <th>Title</th>
          <th>Description</th>
          <th>Due Date</th>
          <th>Completed At</th>
          <th>Archived At</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($archives as $a): ?>
          <tr>
            <td><?= htmlspecialchars($a['title']) ?></td>
            <td><?= htmlspecialchars($a['description']) ?></td>
            <td><?= htmlspecialchars($a['due_date']) ?></td>
            <td><?= htmlspecialchars($a['completed_at']) ?></td>
            <td><?= htmlspecialchars($a['archived_at']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p class="empty">No archived tasks yet.</p>
  <?php endif; ?>

  <a class="back-link" href="task_management.php">← Back to Tasks</a>
</div>
</body>
</html>
