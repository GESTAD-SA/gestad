<?php
date_default_timezone_set('America/Bogota');

session_start();
$action = $_GET['action'] ?? 'login';

// Auto-marcación sin cron: ejecutar en cada request del sitio
try {
  require_once __DIR__ . '/../app/models/AutoAttendanceModel.php';
  // Solo ejecutar si hay sesión iniciada o si estamos en vistas públicas; es liviano y seguro
  AutoAttendanceModel::processNow();
} catch (Throwable $e) {
  // Silenciar errores para no afectar navegación
}

switch($action) {
  case 'login':
    if ($_SERVER['REQUEST_METHOD']=='POST') {
      require_once "../app/controllers/AuthController.php";
      if (AuthController::login($_POST['usuario'], $_POST['password'])) {
        header("Location: index.php?action=dashboard");
      } else {
        $_SESSION['flash_error'] = 'Credenciales incorrectas';
        header("Location: index.php?action=login");
      }
    } else {
      include "../app/views/login.php";
    }
    break;

  case 'logout':
    require_once "../app/controllers/AuthController.php";
    AuthController::logout();
    break;

  case 'dashboard':
    include "../app/views/dashboard.php";
    break;

  case 'manage_admins':
  case 'manage_docentes':
    include "../app/views/users/manage.php";
    break;

  case 'schedules_manage':
    require_once "../app/controllers/ScheduleController.php";
    ScheduleController::manage();
    break;

  case 'schedules_create':
    require_once "../app/controllers/ScheduleController.php";
    ScheduleController::create();
    break;

  case 'schedules_delete':
    require_once "../app/controllers/ScheduleController.php";
    ScheduleController::delete();
    break;

  case 'create_user':
    require_once "../app/controllers/UserController.php";
    $cedula = $_POST['cedula'] ?? null;
    if ($_POST['rol']=='admin') {
      UserController::createAdmin($_POST['nombre'], $_POST['usuario'], $_POST['password'], $_POST['email'], $cedula);
    } else {
      UserController::createDocente($_POST['nombre'], $_POST['usuario'], $_POST['password'], $_POST['email'], $cedula);
    }
    header("Location: index.php?action=".($_POST['rol']=='admin'?'manage_admins':'manage_docentes'));
    break;

  case 'assign_card':
    require_once "../app/controllers/UserController.php";
    UserController::assignCard($_POST['id'], $_POST['uid']);
    header("Location: index.php?action=manage_docentes");
    break;

  

  case 'update_user':
    require_once "../app/controllers/UserController.php";
    $id = $_POST['id'];
    $nombre = $_POST['nombre'] ?? '';
    $usuario = $_POST['usuario'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $cedula = $_POST['cedula'] ?? null;
    UserController::updateUser($id, $nombre, $usuario, $email, $password, $cedula);
    // Redirigir según rol en sesión
    $redirectAction = ($_SESSION['user']['rol'] == 'superadmin') ? 'manage_admins' : 'manage_docentes';
    header("Location: index.php?action=".$redirectAction);
    break;

  case 'delete_user':
    require_once "../app/controllers/UserController.php";
    $id = $_POST['id'];
    UserController::deleteUser($id);
    $redirectAction = ($_SESSION['user']['rol'] == 'superadmin') ? 'manage_admins' : 'manage_docentes';
    header("Location: index.php?action=".$redirectAction);
    break;
    
  case 'deactivate_user':
    require_once "../app/controllers/UserController.php";
    $id = $_POST['id'];
    UserController::deactivateUser($id);
    $redirectAction = ($_SESSION['user']['rol'] == 'superadmin') ? 'manage_admins' : 'manage_docentes';
    header("Location: index.php?action=".$redirectAction);
    break;

  case 'attendance_list':
    require_once "../app/controllers/AttendanceController.php";
    $desde = $_GET['desde'] ?? '';
    $hasta = $_GET['hasta'] ?? '';
    $cedula = $_GET['cedula'] ?? '';
    $asistencias = AttendanceController::listar($desde, $hasta, $cedula);
    include "../app/views/attendance/list.php";
    break;

  case 'report':
    include "../app/views/attendance/report.php";
    break;

  case 'export_report':
    require_once "../app/controllers/ReportController.php";
    ReportController::exportCSV($_GET['desde'], $_GET['hasta']);
    break;

  case 'save_observation':
    require_once "../app/controllers/AttendanceController.php";
    $attendanceId = $_POST['attendance_id'] ?? 0;
    $observacion = $_POST['observacion'] ?? '';
    AttendanceController::saveObservation($attendanceId, $observacion);
    // Redirigir manteniendo filtros si estaban presentes
    $q = [];
    if (!empty($_POST['desde'])) $q[] = 'desde=' . urlencode($_POST['desde']);
    if (!empty($_POST['hasta'])) $q[] = 'hasta=' . urlencode($_POST['hasta']);
    if (!empty($_POST['cedula'])) $q[] = 'cedula=' . urlencode($_POST['cedula']);
    $qs = $q ? ('&' . implode('&', $q)) : '';
    header("Location: index.php?action=attendance_list" . $qs);
    break;

  case 'notifications':
    require_once "../app/controllers/NotificationController.php";
    $notificaciones = NotificationController::listar();
    include "../app/views/notifications/list.php";
    break;

  default:
    include "../app/views/login.php";
}
