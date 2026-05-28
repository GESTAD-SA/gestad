<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/NotificationModel.php';

class AutoAttendanceModel {
    // Este método ya no marca asistencia automáticamente
    // Ahora la asistencia solo se marca al pasar la tarjeta RFID
    public static function processNow(): array {
        // No hacer nada, solo retornar valores por defecto
        return ['tarde' => 0, 'ausente' => 0];
    }
}
