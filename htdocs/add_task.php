<?php
require_once 'config.php';
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$user = currentUser();
$success = $error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $due = $_POST['due_date'] ?? null;
    $add_reminder = isset($_POST['add_reminder']);

    if ($title && $due) {
        try {
            // Insert into tasks
            $stmt = $pdo->prepare("INSERT INTO tasks (user_id, title, description, due_date) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user['id'], $title, $desc, $due]);
            $task_id = $pdo->lastInsertId();

            // Optional reminder
            if ($add_reminder) {
                $msg = "Reminder: Task '$title' is due on $due.";
                $stmt = $pdo->prepare("INSERT INTO reminders (user_id, task_id, remind_date, message) VALUES (?, ?, ?, ?)");
                $stmt->execute([$user['id'], $task_id, $due, $msg]);
            }

            $success = "✅ Task added successfully!";
        } catch (PDOException $e) {
            $error = "❌ Error adding task: " . $e->getMessage();
        }
    } else {
        $error = "⚠️ Please fill in all required fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Task | PlanMyStudy</title>
<link rel="stylesheet" href="style.css">
<style>
/* ===== NAVBAR ===== */
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
  transition: 0.2s;
}
nav a:hover, nav a.active {
  color: #1fb14b;
}

/* ===== BODY ===== */
body {
  background: #ffffff;
  font-family: "Segoe UI", Roboto, Arial, sans-serif;
  margin: 0;
  color: #1a2b4a;
}

/* ===== MAIN FORM ===== */
.container {
  background: white;
  border-radius: 16px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.1);
  width: 90%;
  max-width: 500px;
  margin: 60px auto;
  padding: 40px 35px;
  text-align: center;
}
.container h2 {
  font-size: 26px;
  color: #1a2b4a;
  margin-bottom: 20px;
}
input, textarea {
  width: 100%;
  padding: 12px;
  margin: 10px 0;
  border: 1px solid #ccc;
  border-radius: 8px;
  font-size: 15px;
  outline: none;
  transition: 0.2s;
}
input:focus, textarea:focus {
  border-color: #1fb14b;
  box-shadow: 0 0 4px rgba(31,177,75,0.4);
}
.checkbox-row {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  gap: 8px;
  margin: 12px 0 20px;
}
.checkbox-row input[type="checkbox"] {
  width: 18px;
  height: 18px;
  cursor: pointer;
}
.checkbox-row label {
  font-size: 15px;
  color: #333;
  cursor: pointer;
}
button {
  background: #1fb14b;
  color: white;
  border: none;
  padding: 12px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 15px;
  cursor: pointer;
  transition: 0.2s;
  width: 100%;
  margin-top: 10px;
}
button:hover {
  background: #158c3a;
}
.success, .error {
  font-size: 14px;
  margin-bottom: 10px;
}
.success { color: #1fb14b; }
.error { color: #b32626; }
.back-link {
  display: inline-block;
  margin-top: 15px;
  text-decoration: none;
  color: #1a2b4a;
  font-weight: 500;
}
.back-link:hover {
  text-decoration: underline;
}
</style>
</head>
<body>

<!-- Navbar -->
<header>
  <div class="nav-container">
    <div class="logo">
     <img src="/image/pms.png" alt="PlanMyStudy Logo" class="pms-logo">

      <span>PlanMyStudy</span>
    </div>
    <nav>
      <a href="home.php">Home</a>
      <a href="task_management.php" class="active">Tasks</a>
      <a href="schedule_tracker.php">Schedule</a>
      <a href="shared_tasks.php">Shared</a>
      <a href="reminders.php">Reminders</a>
      <a href="profile.php">Profile</a>
      <a href="logout.php">Logout</a>
    </nav>
  </div>
</header>

<!-- Form -->
<div class="container">
  <h2>Add New Task</h2>

  <?php if ($success): ?><p class="success"><?= htmlspecialchars($success) ?></p><?php endif; ?>
  <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

  <form method="post">
    <input type="text" name="title" placeholder="Task Title" required>
    <textarea name="description" placeholder="Description (optional)"></textarea>
    <input type="date" name="due_date" required>

    <div class="checkbox-row">
      <input type="checkbox" id="add_reminder" name="add_reminder">
      <label for="add_reminder">Add to Reminders</label>
    </div>

    <button type="submit">Add Task</button>
  </form>

  <a class="back-link" href="home.php">← Back to Dashboard</a>
</div>

</body>
</html>
