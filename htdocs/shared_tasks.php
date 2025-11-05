<?php
require 'config.php';
if (!isLoggedIn()) header("Location: login.php");
$user = currentUser($pdo);

$pdo = dbConnect();

// Ensure shared_tasks table has necessary columns
$pdo->exec("ALTER TABLE shared_tasks ADD COLUMN IF NOT EXISTS accepted TINYINT(1) DEFAULT 0");
$pdo->exec("ALTER TABLE shared_tasks ADD COLUMN IF NOT EXISTS owner_done TINYINT(1) DEFAULT 0");
$pdo->exec("ALTER TABLE shared_tasks ADD COLUMN IF NOT EXISTS collaborator_done TINYINT(1) DEFAULT 0");
$pdo->exec("ALTER TABLE shared_tasks ADD COLUMN IF NOT EXISTS completed_at DATETIME NULL");

// Handle toggle
if (isset($_GET['toggle']) && isset($_GET['id'])) {
    $sharedId = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM shared_tasks WHERE id = ?");
    $stmt->execute([$sharedId]);
    $shared = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($shared && $shared['accepted']) {
        if ($user['id'] == $shared['owner_id']) {
            $pdo->prepare("UPDATE shared_tasks SET owner_done = NOT owner_done WHERE id = ?")->execute([$sharedId]);
        } elseif ($user['id'] == $shared['collaborator_id']) {
            $pdo->prepare("UPDATE shared_tasks SET collaborator_done = NOT collaborator_done WHERE id = ?")->execute([$sharedId]);
        }

        $pdo->prepare("
            UPDATE shared_tasks
            SET completed_at = CASE
                WHEN owner_done = 1 AND collaborator_done = 1 THEN NOW()
                ELSE NULL
            END
            WHERE id = ?
        ")->execute([$sharedId]);
    }

    header("Location: shared_tasks.php");
    exit;
}

// Handle new shared task
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['title'])) {
    $title = trim($_POST['title']);
    $desc = trim($_POST['description'] ?? '');
    $priority = $_POST['priority'] ?? 'low';
    $due = !empty($_POST['due']) ? $_POST['due'] . " 23:59:59" : null;

    $pdo->beginTransaction();
    $stmt = $pdo->prepare("INSERT INTO tasks (user_id, title, description, due_date, priority) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$user['id'], $title, $desc, $due, $priority]);
    $taskId = $pdo->lastInsertId();

    if (!empty($_POST['collab_email'])) {
        $coll = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $coll->execute([$_POST['collab_email']]);
        $row = $coll->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $pdo->prepare("
                INSERT INTO shared_tasks (task_id, owner_id, collaborator_id, accepted, owner_done, collaborator_done)
                VALUES (?, ?, ?, 0, 0, 0)
            ")->execute([$taskId, $user['id'], $row['id']]);
        }
    }

    $pdo->commit();
    $success = "✅ Shared task created successfully!";
}

// Fetch all shared tasks
$stmt = $pdo->prepare("
    SELECT st.*, t.title, t.due_date, u.full_name AS owner_name
    FROM shared_tasks st
    JOIN tasks t ON st.task_id = t.id
    JOIN users u ON st.owner_id = u.id
    WHERE st.collaborator_id = ? OR st.owner_id = ?
    ORDER BY st.created_at DESC
");
$stmt->execute([$user['id'], $user['id']]);
$shared = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Shared Tasks | PlanMyStudy</title>
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
  max-width: 1000px;
  margin: 60px auto;
  padding: 40px;
}
h1 { text-align: center; margin-bottom: 15px; color: #1a2b4a; }

.grid-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 25px;
}

/* Cards */
.card {
  background: #fdfdfd;
  border: 1px solid #ddd;
  border-radius: 12px;
  padding: 25px;
}
.card h3 { margin-bottom: 20px; color: #1a2b4a; }

/* List */
.list-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #eee;
  padding: 12px 0;
}
.status {
  font-weight: 600;
  border-radius: 6px;
  padding: 4px 8px;
}
.status.done { color: green; }
.status.pending { color: #b32626; }

/* Buttons */
.btn {
  background: #1fb14b;
  color: white;
  border: none;
  border-radius: 8px;
  padding: 10px 16px;
  cursor: pointer;
  font-weight: 600;
}
.btn:hover { background: #158c3a; }
.small-actions a, .small-actions button {
  font-size: 13px;
  margin-left: 8px;
  text-decoration: none;
  color: #1fb14b;
  border: none;
  background: transparent;
  cursor: pointer;
}

/* Alerts */
.alert.success {
  background: #d6f5dc;
  padding: 12px;
  color: #1b5e20;
  border-radius: 6px;
  margin-bottom: 15px;
}

/* Inputs */
form input, form textarea, form select {
  width: 100%;
  padding: 14px;
  margin-bottom: 16px;
  border: 1px solid #ccc;
  border-radius: 10px;
  font-size: 15px;
  box-sizing: border-box;
}
form textarea {
  resize: vertical;
  min-height: 100px;
}
form input:focus, form textarea:focus, form select:focus {
  border-color: #1fb14b;
  outline: none;
  box-shadow: 0 0 4px rgba(31,177,75,0.3);
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
      <a href="shared_tasks.php" class="active">Shared</a>
      <a href="reminders.php">Reminders</a>
      <a href="profile.php">Profile</a>
      <a href="logout.php">Logout</a>
    </nav>
  </div>
</header>

<main class="container">
  <h1>Shared Tasks Hub</h1>
  <p style="text-align:center; color:#555;">Collaborate and track tasks with others easily.</p>

  <?php if(!empty($success)):?><div class="alert success"><?=$success?></div><?php endif; ?>

  <div class="grid-2">
    <div class="card">
      <h3>Available Shared Tasks</h3>
      <?php if(empty($shared)): ?>
        <p style="color:#777;">No shared tasks yet.</p>
      <?php endif; ?>
      <?php foreach($shared as $s): ?>
        <?php
          $isOwner = ($s['owner_id'] == $user['id']);
          $selfDone = $isOwner ? $s['owner_done'] : $s['collaborator_done'];
          $bothDone = ($s['owner_done'] && $s['collaborator_done']);
        ?>
        <div class="list-item">
          <div>
            <strong><?=htmlspecialchars($s['title'])?></strong><br>
            <small style="color:#777;">Shared by: <?=htmlspecialchars($s['owner_name'])?> — Due: <?=htmlspecialchars($s['due_date'])?></small>
            <div class="status <?= $bothDone ? 'done' : 'pending' ?>">
              <?= $bothDone ? '✅ Completed' : ($selfDone ? 'Waiting for Partner' : 'Pending') ?>
            </div>
          </div>
          <div class="small-actions">
            <?php if(!$s['accepted'] && $s['collaborator_id'] == $user['id']): ?>
              <form method="post" action="accept_shared.php" style="display:inline">
                <input type="hidden" name="id" value="<?=$s['id']?>">
                <button class="btn">Accept</button>
              </form>
            <?php elseif($s['accepted']): ?>
              <a href="shared_tasks.php?toggle=1&id=<?=$s['id']?>" class="btn small">Mark / Unmark</a>
            <?php else: ?>
              <span style="font-size:13px;color:#999;">Awaiting acceptance</span>
            <?php endif; ?>
            <a href="view_task.php?id=<?=$s['task_id']?>" class="btn small">View</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="card">
      <h3>Share a New Task</h3>
      <form method="post">
        <input name="title" placeholder="Task Title" required>
        <textarea name="description" placeholder="Description (optional)"></textarea>
        <input name="collab_email" placeholder="Collaborator Email" required>
        <input name="due" type="date">
        <select name="priority">
          <option value="low">Low</option>
          <option value="medium">Medium</option>
          <option value="high">High</option>
        </select>
        <button class="btn">Share Task</button>
      </form>
    </div>
  </div>
</main>
</body>
</html>
