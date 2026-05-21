<?php
require_once __DIR__ . "/../models/AttendanceModel.php";

class AttendanceController {
    public static function listar($desde, $hasta, $identificacion = '') {
        return AttendanceModel::getByRangeFiltered($desde, $hasta, trim($identificacion));
    }

    public static function saveObservation($attendanceId, $texto) {
        if (($_SESSION['user']['rol'] ?? '') !== 'admin') die("No autorizado");
        $attendanceId = (int)$attendanceId;
        $texto = trim($texto);
        return AttendanceModel::addOrUpdateObservation($attendanceId, $texto);
    }
}