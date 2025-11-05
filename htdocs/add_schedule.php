<?php
require_once 'config.php';
$user = currentUser();
if (!$user) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['event_title']);
    $desc = trim($_POST['description']);
    $date = $_POST['schedule_date'];

    if ($title && $date) {
        $pdo = dbConnect();
        $stmt = $pdo->prepare("INSERT INTO schedules (user_id, event_title, description, schedule_date) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user['id'], $title, $desc, $date]);
        header("Location: schedule_tracker.php");
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
<title>Add Schedule | PlanMyStudy</title>
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
  <h2>Add New Event</h2>
  <?php if (!empty($error)): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <form method="post">
    <input type="text" name="event_title" placeholder="Event Title" required>
    <textarea name="description" placeholder="Description (optional)"></textarea>
    <input type="date" name="schedule_date" required>
    <button type="submit">Save Event</button>
  </form>
  <a href="schedule_tracker.php">← Back to Schedule</a>
</div>
</body>
</html>
