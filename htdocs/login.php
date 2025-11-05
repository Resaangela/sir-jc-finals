<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = strtolower(trim($_POST['role'] ?? 'user'));

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(email) = LOWER(?) AND LOWER(role) = LOWER(?)");
        $stmt->execute([$email, $role]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: admin_panel.php");
            } else {
                header("Location: home.php");
            }
            exit;
        } else {
            $error = "Invalid email, password, or role.";
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Login | PlanMyStudy</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
/* ==== BODY ==== */
body {
  background-color: #ffffff;
  font-family: "Segoe UI", Roboto, Arial, sans-serif;
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  margin: 0;
}

/* ==== CONTAINER ==== */
.login-wrapper {
  background: #fff;
  width: 420px;
  border-radius: 16px;
  box-shadow: 0 6px 20px rgba(0,0,0,0.15);
  padding: 40px 35px;
  text-align: center;
}

/* ==== LOGO ==== */
.logo {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  margin-bottom: 15px;
}
.logo img {
  width: 45px;
  height: 45px;
  border-radius: 50%;
}
.logo span {
  font-size: 22px;
  font-weight: 700;
  color: #1a2b4a;
}

/* ==== TEXT ==== */
h2 {
  margin: 10px 0 5px;
  font-size: 26px;
  color: #1a2b4a;
}
p {
  color: #8693a5;
  margin-bottom: 25px;
}

/* ==== FORM ==== */
form {
  display: flex;
  flex-direction: column;
  gap: 15px;
}
input, select {
  padding: 12px;
  font-size: 15px;
  border: 1px solid #d8dee6;
  border-radius: 8px;
  outline: none;
  transition: 0.2s;
}
input:focus, select:focus {
  border-color: #1fb14b;
  box-shadow: 0 0 4px rgba(31,177,75,0.4);
}

/* ==== BUTTON ==== */
.btn {
  padding: 12px;
  border: none;
  background:  #1a2b4a;
  color: white;
  border-radius: 8px;
  cursor: pointer;
  font-size: 16px;
  font-weight: 600;
  transition: 0.2s;
}
.btn:hover {
  background: #158c3a;
}

/* ==== ALERTS ==== */
.error {
  background: #fdecea;
  color: #b32626;
  padding: 10px;
  border-radius: 6px;
  margin-bottom: 15px;
}
.success {
  background: #e6f9ee;
  color: #1c7532;
  padding: 10px;
  border-radius: 6px;
  margin-bottom: 15px;
}

/* ==== LINKS ==== */
.links {
  margin-top: 20px;
  display: flex;
  justify-content: space-between;
  font-size: 14px;
}
.links a {
  color: #1a2b4a;;
  text-decoration: none;
  font-weight: 500;
}
.links a:hover {
  text-decoration: underline;
}

/* ==== MOBILE ==== */
@media (max-width: 480px) {
  .login-wrapper {
    width: 90%;
    padding: 30px 20px;
  }
}
</style>
</head>
<body>

<div class="login-wrapper">
 <div class="logo">
    <img src="/image/pms.png" alt="PlanMyStudy Logo" class="pms-logo">

      <span>PlanMyStudy</span>
    </div>
  <h2>Welcome Back!</h2>
  <p>Log in to your PlanMyStudy account</p>

  <?php if(isset($_GET['registered'])): ?>
    <div class="success">✅ Account created successfully! Please log in.</div>
  <?php endif; ?>

  <?php if(!empty($error)): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="post">
    <input type="email" name="email" placeholder="Email Address" required>
    <input type="password" name="password" placeholder="Password" required>
    <select name="role" required>
      <option value="user">User</option>
      <option value="admin">Admin</option>
    </select>
    <button class="btn" type="submit">Login</button>
  </form>

  <div class="links">
    <a href="register.php">Create an account</a>
    <a href="forgot_password.php">Forgot password?</a>
  </div>
</div>

</body>
</html>
