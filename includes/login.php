<?php
session_start();
if (isset($_SESSION['user_id']) && isset($_SESSION['user_type']) && intval($_SESSION['user_type']) === 2) {
    header('Location: dashboard.php');
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Staff Login — LINYA</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="../css/login.css">
</head>
<body>
  <main class="login-wrap">
    <form id="loginForm" class="login-card" autocomplete="off">
      <h1 class="brand">LINYA</h1>
      <!-- <p class="hint">Staff login — enter credentials (case sensitive).</p> -->

      <label class="field">
        <span class="label">Username</span>
        <input id="username" name="user_name" type="text" autocomplete="off" required />
      </label>

      <label class="field">
        <span class="label">Password</span>
        <input id="password" name="password" type="password" autocomplete="new-password" required />
      </label>

      <div id="error" class="error" aria-live="polite" style="display:none"></div>

      <div class="actions">
        <button id="submitBtn" type="submit" class="btn primary">Sign in</button>
      </div>

      <!-- <div class="small muted foot">Only staff accounts can access the queue window.</div> -->
</form>
    <div class="imageContainer">
      <img src="/LATESTLINYA/resources/ucc.png" alt="University Logo" class="image">
    </div>
  </main>

  <script src="../js/login.js"></script>
</body>
</html>