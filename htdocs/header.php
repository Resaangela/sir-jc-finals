<?php
$darkMode = isset($_SESSION['user_id']) ? (currentUser()['dark_mode'] ?? 0) : 0;
?>
<header class="pms-header <?= $darkMode ? 'dark' : '' ?>">
  <div class="pms-header-inner">
    <div class="left">
      <!-- ✅ Root-relative path -->
      <img src="/image/pms.png" alt="PlanMyStudy Logo" class="pms-logo">
      <h1 class="pms-title">PlanMyStudy</h1>
    </div>

    <nav class="pms-nav">
      <a href="home.php">🏠 Home</a>
      <a href="task_management.php">📋 Tasks</a>
      <a href="schedule_tracker.php">📅 Schedule</a>
      <a href="shared_tasks.php">🤝 Shared</a>
      <a href="reminders.php">⏰ Reminders</a>
      <a href="profile.php">👤 Profile</a>
      <a href="logout.php">🚪 Logout</a>
    </nav>

    <?php if (isLoggedIn()): ?>
      <form method="post" action="toggle_darkmode.php" style="display:inline;">
        <input type="hidden" name="dark" value="<?= $darkMode ? 0 : 1 ?>">
        <button class="btn small" title="Toggle Dark Mode" style="background:none;color:white;border:none;cursor:pointer;">
          <?= $darkMode ? '☀️' : '🌙' ?>
        </button>
      </form>
    <?php endif; ?>
  </div>
</header>
