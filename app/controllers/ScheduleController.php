<?php
require_once __DIR__ . '/../models/Database.php';
require_once __DIR__ . '/../models/ScheduleModel.php';
require_once __DIR__ . '/../models/UserModel.php';

class ScheduleController {
    public static function manage() {
        // Solo admin o superadmin
        $rol = $_SESSION['user']['rol'] ?? '';
        if (!in_array($rol, ['admin','superadmin'])) die('No autorizado');
        $schedules = ScheduleModel::listAll();
        $docentes = UserModel::listByRole('docente');
        include __DIR__ . '/../views/schedules/manage.php';
    }

    public static function create() {
        $rol = $_SESSION['user']['rol'] ?? '';
        if (!in_array($rol, ['admin','superadmin'])) die('No autorizado');
        $docenteId = (int)($_POST['docente_id'] ?? 0);
        $dia = (int)($_POST['dia_semana'] ?? 0);
        $horaInicio = trim($_POST['hora_inicio'] ?? '');
        $horaFin = trim($_POST['hora_fin'] ?? '');
        $salon = trim($_POST['salon'] ?? '');

        if ($docenteId <= 0 || $dia < 1 || $dia > 6 || $horaInicio === '' || $horaFin === '') {
            $_SESSION['flash_error'] = 'Datos incompletos para crear el horario.';
            header('Location: index.php?action=schedules_manage');
            return;
        }

        try {
            ScheduleModel::assignWeeklyBlock($docenteId, $dia, $horaInicio, $horaFin, $salon ?: null);
            $_SESSION['flash_success'] = 'Horario guardado correctamente.';
        } catch (Throwable $e) {
            $_SESSION['flash_error'] = 'Error al guardar: ' . $e->getMessage();
        }
        header('Location: index.php?action=schedules_manage');
    }

    public static function delete() {
        $rol = $_SESSION['user']['rol'] ?? '';
        if (!in_array($rol, ['admin','superadmin'])) die('No autorizado');
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['flash_error'] = 'ID inválido.';
            header('Location: index.php?action=schedules_manage');
            return;
        }
        try {
            ScheduleModel::deleteById($id);
            $_SESSION['flash_success'] = 'Horario eliminado.';
        } catch (Throwable $e) {
            $_SESSION['flash_error'] = 'Error al eliminar: ' . $e->getMessage();
        }
        header('Location: index.php?action=schedules_manage');
    }
}
