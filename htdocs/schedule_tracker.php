<?php
require_once 'config.php';
$user = currentUser();
if (!$user) {
    header("Location: login.php");
    exit;
}

$pdo = dbConnect();

// ✅ Handle mark-as-done / undo (restart)
if (isset($_GET['toggle']) && isset($_GET['id'])) {
    $taskId = (int)$_GET['id'];
    $pdo->prepare("UPDATE schedules SET is_done = 1 - is_done WHERE id = ? AND user_id = ?")
        ->execute([$taskId, $user['id']]);
    header("Location: schedule_tracker.php");
    exit;
}

// ✅ Handle delete
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $taskId = (int)$_GET['id'];
    $pdo->prepare("DELETE FROM schedules WHERE id = ? AND user_id = ?")
        ->execute([$taskId, $user['id']]);
    header("Location: schedule_tracker.php");
    exit;
}

// ✅ Handle adding new event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_title'])) {
    $title = trim($_POST['event_title']);
    $desc = trim($_POST['description'] ?? '');
    $date = $_POST['schedule_date'] ?? '';

    if ($title && $date) {
        $stmt = $pdo->prepare("INSERT INTO schedules (user_id, event_title, description, schedule_date, is_done) VALUES (?, ?, ?, ?, 0)");
        $stmt->execute([$user['id'], $title, $desc, $date]);
    }
    header("Location: schedule_tracker.php");
    exit;
}

// ✅ Fetch schedules
$stmt = $pdo->prepare("SELECT * FROM schedules WHERE user_id = ? ORDER BY schedule_date ASC");
$stmt->execute([$user['id']]);
$schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare events by date for calendar
$eventsByDate = [];
foreach ($schedules as $s) {
    $d = date('Y-m-d', strtotime($s['schedule_date']));
    $eventsByDate[$d][] = $s;
}

// Calendar setup
$month = $_GET['month'] ?? date('n');
$year = $_GET['year'] ?? date('Y');
$firstDay = mktime(0, 0, 0, $month, 1, $year);
$daysInMonth = date('t', $firstDay);
$monthName = date('F', $firstDay);
$startDay = date('w', $firstDay);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Schedule Tracker | PlanMyStudy</title>
<link rel="stylesheet" href="style.css">
<style>
body {
  background: #ffffff;
  font-family: "Segoe UI", Roboto, Arial, sans-serif;
  margin: 0;
  color: #1a2b4a;
}

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
.logo img { width: 40px; height: 40px; border-radius: 50%; }
.logo span { font-weight: 700; font-size: 20px; color: white; }
nav a {
  text-decoration: none;
  color: white;
  font-weight: 500;
  margin-left: 18px;
  transition: 0.2s;
}
nav a:hover, nav a.active { color: #1fb14b; }

/* ===== MAIN CONTAINER ===== */
.container {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  width: 90%;
  max-width: 1400px;
  margin: 40px auto;
  gap: 20px;
}

/* ===== CALENDAR ===== */
.calendar-container {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
  padding: 25px;
  width: 50%;
}
.calendar {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}
.calendar th {
  background: #1a2b4a;
  color: white;
  padding: 10px;
}
.calendar td {
  width: 14.28%;
  height: 90px;
  border: 1px solid #ddd;
  text-align: right;
  vertical-align: top;
  padding: 5px;
  cursor: pointer;
  transition: 0.2s;
}
.calendar td:hover { background: #f0f9f4; }
.event-day { background: #e6f9ef; border-left: 4px solid #1fb14b; }
.calendar small {
  display: block;
  text-align: left;
  font-size: 12px;
  color: #1a2b4a;
  margin-top: 3px;
}
.month-nav {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.month-nav a {
  text-decoration: none;
  color: #1fb14b;
  font-weight: bold;
}
.month-nav a:hover { text-decoration: underline; }

/* ===== RIGHT PANEL (Add + List) ===== */
.right-panel {
  width: 45%;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

/* ADD EVENT */
.sidebar {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
  padding: 25px;
}
.sidebar h3 {
  color: #1a2b4a;
  margin-bottom: 15px;
  text-align: center;
}
.sidebar input, .sidebar textarea {
  width: 100%;
  padding: 10px;
  margin-bottom: 12px;
  border: 1px solid #ccc;
  border-radius: 8px;
  font-size: 15px;
}
.sidebar button {
  background: #1fb14b;
  color: white;
  border: none;
  padding: 10px;
  border-radius: 8px;
  width: 100%;
  cursor: pointer;
  font-weight: 600;
}
.sidebar button:hover { background: #158c3a; }

/* SCHEDULE LIST */
.pending-panel {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
  padding: 25px;
}
.pending-panel h3 {
  text-align: center;
  color: #1a2b4a;
  margin-bottom: 15px;
}
.schedule-item {
  border: 1px solid #e0e0e0;
  border-left: 5px solid #1fb14b;
  padding: 10px;
  border-radius: 8px;
  margin-bottom: 10px;
}
.schedule-item.finished { border-left: 5px solid #1a2b4a; opacity: 0.8; }
.schedule-item p { margin: 5px 0; font-size: 14px; }
.schedule-item strong { color: #1a2b4a; }
.actions a {
  margin-right: 8px;
  text-decoration: none;
  font-size: 13px;
  color: #1fb14b;
  font-weight: 600;
}
.actions a.delete { color: #b32626; }
.actions a:hover { text-decoration: underline; }
</style>
<script>
function openSidebar(date) {
  document.getElementById('schedule_date').value = date;
  document.getElementById('selectedDate').innerText = date;
}
</script>
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
      <a href="schedule_tracker.php" class="active">Schedule</a>
      <a href="shared_tasks.php">Shared</a>
      <a href="reminders.php">Reminders</a>
      <a href="profile.php">Profile</a>
      <a href="logout.php">Logout</a>
    </nav>
  </div>
</header>

<div class="container">
  <!-- CALENDAR -->
  <div class="calendar-container">
    <div class="month-nav">
      <a href="?month=<?= ($month == 1) ? 12 : $month - 1 ?>&year=<?= ($month == 1) ? $year - 1 : $year ?>">&laquo; Prev</a>
      <h2><?= $monthName . ' ' . $year ?></h2>
      <a href="?month=<?= ($month == 12) ? 1 : $month + 1 ?>&year=<?= ($month == 12) ? $year + 1 : $year ?>">Next &raquo;</a>
    </div>

    <table class="calendar">
      <tr>
        <th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th>
        <th>Thu</th><th>Fri</th><th>Sat</th>
      </tr>
      <tr>
        <?php
        $dayCount = 0;
        for ($i = 0; $i < $startDay; $i++) {
            echo "<td></td>";
            $dayCount++;
        }
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date = sprintf('%04d-%02d-%02d', $year, $month, $d);
            $hasEvent = isset($eventsByDate[$date]);
            $class = $hasEvent ? 'event-day' : '';
            echo "<td class='$class' onclick='openSidebar(\"$date\")'>";
            echo "<div><strong>$d</strong></div>";
            if ($hasEvent) {
                foreach ($eventsByDate[$date] as $ev) {
                    echo "<small>" . htmlspecialchars($ev['event_title']) . "</small>";
                }
            }
            echo "</td>";
            $dayCount++;
            if ($dayCount % 7 == 0) echo "</tr><tr>";
        }
        while ($dayCount % 7 != 0) {
            echo "<td></td>";
            $dayCount++;
        }
        ?>
      </tr>
    </table>
  </div>

  <!-- RIGHT PANEL -->
  <div class="right-panel">
    <!-- ADD EVENT PANEL -->
    <div class="sidebar">
      <h3>Add Event</h3>
      <form method="post">
        <label>Date: <span id="selectedDate" style="font-weight:bold;">Select a day</span></label>
        <input type="hidden" id="schedule_date" name="schedule_date" required>
        <input type="text" name="event_title" placeholder="Event Title" required>
        <textarea name="description" placeholder="Description"></textarea>
        <button type="submit">Save Event</button>
      </form>
    </div>

    <!-- PENDING EVENTS BELOW -->
    <div class="pending-panel">
      <h3>Your Schedules</h3>
      <?php if (count($schedules) > 0): ?>
        <?php foreach ($schedules as $s): ?>
          <div class="schedule-item <?= $s['is_done'] ? 'finished' : '' ?>">
            <strong><?= htmlspecialchars($s['event_title']) ?></strong>
            <p><?= htmlspecialchars($s['description']) ?></p>
            <p><small><?= htmlspecialchars($s['schedule_date']) ?></small></p>
            <div class="actions">
              <a href="edit_schedule.php?id=<?= $s['id'] ?>">Edit</a>
              <a href="schedule_tracker.php?toggle=1&id=<?= $s['id'] ?>">
                <?= $s['is_done'] ? 'Restart' : 'Mark Done' ?>
              </a>
              <a href="schedule_tracker.php?delete=1&id=<?= $s['id'] ?>" class="delete" onclick="return confirm('Delete this schedule?')">Delete</a>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p style="text-align:center; color:#555;">No schedules yet.</p>
      <?php endif; ?>
    </div>
  </div>
</div>
</body>
</html>
