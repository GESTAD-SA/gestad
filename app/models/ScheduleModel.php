<?php
require_once "Database.php";

class ScheduleModel {
    
    public static function assignSchedule($docenteId, $horaInicio) {
        $db = Database::connect();
        // Inserta un horario genérico sin día/fin/salón
        $stmt = $db->prepare("REPLACE INTO schedules(docente_id, hora_inicio) VALUES(?,?)");
        return $stmt->execute([$docenteId, $horaInicio]);
    }

    // Nuevo: asignar bloque semanal con día (1=Lunes..6=Sábado), hora inicio/fin y salon
    public static function assignWeeklyBlock($docenteId, int $diaSemana, string $horaInicio, string $horaFin, ?string $salon = null) {
        $db = Database::connect();
        $stmt = $db->prepare("REPLACE INTO schedules(docente_id, dia_semana, hora_inicio, hora_fin, salon) VALUES(?,?,?,?,?)");
        return $stmt->execute([$docenteId, $diaSemana, $horaInicio, $horaFin, $salon]);
    }

    public static function getActiveBlockForTeacher($docenteId, \DateTime $now): ?array {
        $db = Database::connect();
        // 1=Lunes .. 7=Domingo, normalizamos a 1..6 (L-S)
        $w = (int)$now->format('N');
        $hora = $now->format('H:i:s');
        $stmt = $db->prepare("SELECT * FROM schedules WHERE docente_id=? AND dia_semana=? AND ? BETWEEN hora_inicio AND hora_fin LIMIT 1");
        $stmt->execute([$docenteId, $w, $hora]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function getStartBlockForTeacherToday($docenteId, \DateTime $now): ?array {
        $db = Database::connect();
        $w = (int)$now->format('N');
        $stmt = $db->prepare("SELECT * FROM schedules WHERE docente_id=? AND dia_semana=? ORDER BY hora_inicio ASC");
        $stmt->execute([$docenteId, $w]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $rows[0] ?? null;
    }

    public static function listAll(): array {
        $db = Database::connect();
        $sql = "SELECT s.*, u.nombre AS docente_nombre FROM schedules s JOIN users u ON u.id=s.docente_id ORDER BY s.dia_semana ASC, s.hora_inicio ASC";
        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function deleteById(int $id): bool {
        $db = Database::connect();
        $stmt = $db->prepare("DELETE FROM schedules WHERE id=?");
        return $stmt->execute([$id]);
    }
}
