<?php
require_once 'config.php';
$user = currentUser();
if (!$user) {
    header("Location: login.php");
    exit;
}

$pdo = dbConnect();

// Get all users (except self) to share with
$stmt = $pdo->prepare("SELECT id, full_name, student_id FROM users WHERE id != ? AND role = 'user'");
$stmt->execute([$user['id']]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $due = $_POST['due_date'] ?? null;
    $partner_id = (int)($_POST['partner_id'] ?? 0);

    if ($title && $partner_id) {
        $stmt = $pdo->prepare("
            INSERT INTO shared_tasks (creator_id, partner_id, title, description, due_date)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$user['id'], $partner_id, $title, $desc, $due]);
        header("Location: shared_tasks.php");
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
<title>Add Shared Task</title>
<style>
body {
  font-family: "Segoe UI", Roboto, Arial;
  background: #f8fbf9;
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
}
.container {
  background: white;
  border-radius: 12px;
  padding: 40px;
  width: 420px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}
input, textarea, select {
  width: 100%;
  margin-bottom: 15px;
  padding: 10px;
  border-radius: 8px;
  border: 1px solid #ccc;
}
button {
  width: 100%;
  background: #1fb14b;
  color: white;
  border: none;
  padding: 12px;
  border-radius: 8px;
  font-size: 16px;
  cursor: pointer;
}
button:hover { background: #158c3a; }
</style>
</head>
<body>
    

<div class="container">
  <h2>Create Shared Task</h2>
  <?php if(!empty($error)): ?><p style="color:red;"><?= htmlspecialchars($error) ?></p><?php endif; ?>

  <form method="post">
    <input type="text" name="title" placeholder="Task Title" required>
    <textarea name="description" placeholder="Task Description"></textarea>
    <input type="date" name="due_date">
    <select name="partner_id" required>
      <option value="">Select Student</option>
      <?php foreach ($users as $u): ?>
        <option value="<?= $u['id'] ?>">
          <?= htmlspecialchars($u['full_name']) ?> (<?= htmlspecialchars($u['student_id']) ?>)
        </option>
      <?php endforeach; ?>
    </select>
    <button type="submit">Share Task</button>
  </form>
  <a href="shared_tasks.php" style="text-decoration:none;color:#1fb14b;">← Back to Shared Tasks</a>
</div>
</body>
</html>
