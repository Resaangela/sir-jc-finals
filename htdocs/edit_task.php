<?php
require_once 'config.php';
$user = currentUser();
if (!$user) {
    header("Location: login.php");
    exit;
}

$pdo = dbConnect();

if (!isset($_GET['id'])) {
    header("Location: task_management.php");
    exit;
}

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user['id']]);
$task = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$task) {
    echo "Task not found.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);
    $due = $_POST['due_date'];

    if ($title && $due) {
        $update = $pdo->prepare("UPDATE tasks SET title = ?, description = ?, due_date = ? WHERE id = ? AND user_id = ?");
        $update->execute([$title, $desc, $due, $id, $user['id']]);
        header("Location: task_management.php");
        exit;
    } else {
        $error = "Please fill in all required fields.";
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Edit Task | PlanMyStudy</title>
<style>
body {
  background: #e9f7ee;
  font-family: "Segoe UI", Roboto, Arial, sans-serif;
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
}
.form-container {
  background: white;
  padding: 35px;
  border-radius: 12px;
  box-shadow: 0 6px 16px rgba(0,0,0,0.1);
  width: 400px;
}
input, textarea {
  width: 100%;
  padding: 10px;
  margin-bottom: 15px;
  border: 1px solid #ccc;
  border-radius: 6px;
}
button {
  background: #1fb14b;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  cursor: pointer;
}
button:hover {
  background: #158c3a;
}
.error { color: red; margin-bottom: 10px; text-align: center; }
a { text-decoration: none; color: #1fb14b; display: block; text-align: center; margin-top: 10px; }
</style>
</head>
<body>
   

<div class="form-container">
  <h2>Edit Task</h2>
  <?php if (!empty($error)): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <form method="post">
    <input type="text" name="title" value="<?= htmlspecialchars($task['title']) ?>" required>
    <textarea name="description"><?= htmlspecialchars($task['description']) ?></textarea>
    <input type="date" name="due_date" value="<?= htmlspecialchars($task['due_date']) ?>" required>
    <button type="submit">Save Changes</button>
  </form>
  <a href="task_management.php">← Back to Tasks</a>
</div>
</body>
</html>
