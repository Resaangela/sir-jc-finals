<?php
require_once 'config.php';

// ✅ Check if user is logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$user = currentUser(); // Fetch logged-in user's data

// ✅ Fallbacks
$userName = $user['full_name'] ?? 'User';
$userRole = strtolower($user['role'] ?? 'unknown');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Home | PlanMyStudy</title>
<link rel="stylesheet" href="style.css">
<style>
/* ===== NAVBAR ===== */
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

/* ===== PAGE STYLE ===== */
body {
  background: #ffffff;
  font-family: "Segoe UI", Roboto, Arial, sans-serif;
  margin: 0;
  color: #1a2b4a;
}

/* ===== DASHBOARD ===== */
.dashboard {
  text-align: center;
  padding: 60px 20px;
  max-width: 1100px;
  margin: auto;
}
.dashboard h2 {
  font-size: 28px;
  color: #1a2b4a;
}
.dashboard p {
  color: #555;
  font-size: 16px;
  margin-bottom: 40px;
}

/* ===== CARDS ===== */
.cards {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 30px;
}
.card {
  background: white;
  border-radius: 16px;
  padding: 30px;
  width: 280px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.1);
  transition: transform 0.2s, box-shadow 0.2s;
  text-align: center;
}
.card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}
.card h3 {
  color: #1a2b4a;
  margin-bottom: 10px;
}
.card p {
  color: #666;
  font-size: 14px;
  margin-bottom: 20px;
}
.card .btn {
  background: #1fb14b;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: 0.2s;
}
.card .btn:hover {
  background: #158c3a;
}

/* ===== USER WELCOME ===== */
.welcome {
  text-align: right;
  font-size: 15px;
  color: white;
}
.welcome span {
  font-weight: 600;
  color: #1fb14b;
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
      <a href="home.php" class="active">Home</a>
      <a href="profile.php">Profile</a>
      <a href="logout.php">Logout</a>
    </nav>
  </div>
</header>

<div class="dashboard">
  <h2>Welcome, <?= htmlspecialchars($userName) ?>!</h2>
  <p>What would you like to do today?</p>

  <div class="cards">
    <div class="card">
      <h3>Add Task</h3>
      <p>Create and organize a new task.</p>
      <a href="add_task.php"><button class="btn">Go</button></a>
    </div>

    <div class="card">
      <h3>Shared Tasks</h3>
      <p>Collaborate and manage shared tasks.</p>
      <a href="shared_tasks.php"><button class="btn">Go</button></a>
    </div>

    <div class="card">
      <h3>Schedule Tracker</h3>
      <p>View your academic schedule.</p>
      <a href="schedule_tracker.php"><button class="btn">Go</button></a>
    </div>

    <div class="card">
      <h3>Task Management</h3>
      <p>Manage all your active and past tasks.</p>
      <a href="task_management.php"><button class="btn">Go</button></a>
    </div>

    <?php if ($userRole === 'admin'): ?>
    <div class="card">
      <h3>Admin Panel</h3>
      <p>Manage user accounts and app settings.</p>
      <a href="admin_panel.php"><button class="btn">Go</button></a>
    </div>
    <?php endif; ?>

    <div class="card">
      <h3>Reminders</h3>
      <p>Check your upcoming task deadlines.</p>
      <a href="reminders.php"><button class="btn">Go</button></a>
    </div>
  </div>
</div>

</body>
</html>
