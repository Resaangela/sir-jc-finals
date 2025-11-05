<?php
require_once 'config.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if ($email) {
        // Normally you'd send a reset link via email here.
        $message = "If an account exists with this email, a reset link has been sent.";
    } else {
        $message = "Please enter a valid email.";
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Forgot Password | PlanMyStudy</title>
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

/* ==== WRAPPER ==== */
.forgot-wrapper {
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

/* ==== INPUT ==== */
input {
  width: 100%;
  padding: 12px;
  font-size: 15px;
  border: 1px solid #d8dee6;
  border-radius: 8px;
  outline: none;
  margin-bottom: 15px;
  transition: 0.2s;
}
input:focus {
  border-color: #1fb14b;
  box-shadow: 0 0 4px rgba(31,177,75,0.4);
}

/* ==== BUTTON ==== */
.btn {
  width: 100%;
  padding: 12px;
  border: none;
  background: #1a2b4a;
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

/* ==== MESSAGE ==== */
.message {
  background: #e6f9ef;
  color: #1a2b4a;
  padding: 10px;
  border-radius: 6px;
  margin-bottom: 15px;
}

/* ==== LINKS ==== */
.links {
  margin-top: 20px;
  text-align: center;
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
  .forgot-wrapper {
    width: 90%;
    padding: 30px 20px;
  }
}
</style>
</head>
<body>

<div class="forgot-wrapper">
  <div class="logo">
<img src="/image/pms.png" alt="PlanMyStudy Logo" class="pms-logo">

    <span>PlanMyStudy</span>
  </div>

  <h2>Forgot Password?</h2>
  <p>Enter your registered email and we’ll send you reset instructions.</p>

  <?php if(!empty($message)): ?>
    <div class="message"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <form method="post">
    <input type="email" name="email" placeholder="Email Address" required>
    <button class="btn">Send Reset Link</button>
  </form>

  <div class="links">
    <a href="login.php">← Back to Login</a>
  </div>
</div>

</body>
</html>
