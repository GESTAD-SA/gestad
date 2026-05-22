<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>gestad</title>
  <link rel="stylesheet" href="assets/css/pastel-green.css">
  <link rel="stylesheet" href="assets/css/header.css">
</head>
<body>
<header>
  <div class="container header-inner">
    <h1>📋GESTAD</h1>
    <nav>
      <ul>
        <li><a href="index.php?action=dashboard">Inicio</a></li>
        <?php if($_SESSION['user']['rol']=='superadmin'): ?>
          <li><a href="index.php?action=manage_admins">Gestionar Admins</a></li>
          <li><a href="index.php?action=schedules_manage">Horarios</a></li>
          <li><a href="index.php?action=attendance_list">Asistencias</a></li>
          <li><a href="index.php?action=report">Reportes</a></li>
        <?php elseif($_SESSION['user']['rol']=='admin'): ?>
          <li><a href="index.php?action=manage_docentes">Gestionar Docentes</a></li>
          <li><a href="index.php?action=schedules_manage">Horarios</a></li>
          <li><a href="index.php?action=notifications">Notificaciones</a></li>
          <li><a href="index.php?action=attendance_list">Asistencias</a></li>
          <li><a href="index.php?action=report">Reportes</a></li>
        <?php else: ?>
          <li><a href="index.php?action=attendance_list">Asistencias</a></li>
        <?php endif; ?>
        <li><a href="index.php?action=logout">Cerrar Sesión</a></li>
      </ul>
    </nav>
  </div>
</header>
<main class="container">
  <?php if(!empty($_SESSION['flash_success']) || !empty($_SESSION['flash_error'])): ?>
    <div class="card" style="border-left:4px solid <?php echo !empty($_SESSION['flash_error']) ? '#e55353' : '#6cc36c'; ?>;">
      <?php if(!empty($_SESSION['flash_success'])): ?>
        <p style="margin:0; color:#204c24;"><strong><?php echo htmlspecialchars($_SESSION['flash_success']); ?></strong></p>
      <?php endif; ?>
      <?php if(!empty($_SESSION['flash_error'])): ?>
        <p style="margin:0; color:#a13232;"><strong><?php echo htmlspecialchars($_SESSION['flash_error']); ?></strong></p>
      <?php endif; ?>
    </div>
    <?php unset($_SESSION['flash_success'], $_SESSION['flash_error']); ?>
  <?php endif; ?>
