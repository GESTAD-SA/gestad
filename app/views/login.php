<?php
// Asegurarse de que la sesión esté iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Login RFID</title>
  <link rel="stylesheet" href="assets/css/pastel-green.css">
</head>
<body>
  <div class="center">
    <div class="card login-card">
      <h2>Iniciar Sesión</h2>
      <?php if (isset($_SESSION['flash_error'])): ?>
        <div style="background: #ffebee; color: #c62828; padding: 10px; margin-bottom: 15px; border-radius: 4px; font-size: 14px;">
          <?php 
            echo htmlspecialchars($_SESSION['flash_error']); 
            unset($_SESSION['flash_error']);
          ?>
        </div>
      <?php endif; ?>
      <form method="post" action="index.php?action=login">
        <div class="form-row">
          <input type="text" name="usuario" placeholder="Usuario" required>
        </div>
        <div class="form-row">
          <input type="password" name="password" placeholder="Contraseña" required>
        </div>
        <button type="submit" class="btn">Ingresar</button>
      </form>
    </div>
  </div>
</body>
</html>
