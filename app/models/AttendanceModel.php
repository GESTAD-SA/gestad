<?php
require_once __DIR__ . "/Database.php";
require_once __DIR__ . "/UserModel.php";
require_once __DIR__ . "/NotificationModel.php";
require_once __DIR__ . "/ScheduleModel.php";

class AttendanceModel {
    public static function marcarAsistencia($uid) {
        $db = Database::connect();
        // Normalizar UID entrante para que coincida con lo almacenado en users.uid_tarjeta
        $uid = strtoupper(trim($uid));
        $user = UserModel::findByUID($uid);
        if (!$user) return false;

        $docenteId = $user['id'];
        $now = new \DateTime('now');
        $w = (int)$now->format('N'); // 1=Lunes..7=Domingo
        if ($w < 1 || $w > 6) {
            // Solo de lunes a sábado (incluido)
            return false;
        }

        $fecha = $now->format('Y-m-d');
        $horaActual = $now->format('H:i:s');

        // Buscar bloque ACTIVO de hoy del docente (hora actual dentro del rango)
        $bloque = ScheduleModel::getActiveBlockForTeacher($docenteId, $now);
        if (!$bloque) {
            // No hay bloque activo en este momento
            return false;
        }

        $inicio = \DateTime::createFromFormat('H:i:s', $bloque['hora_inicio']);
        $fin = isset($bloque['hora_fin']) && $bloque['hora_fin'] ? \DateTime::createFromFormat('H:i:s', $bloque['hora_fin']) : null;
        if (!$inicio) {
            return false;
        }

        // Evitar duplicado: si ya existe registro hoy dentro del rango del bloque
        if ($fin) {
            $stmt = $db->prepare("SELECT 1 FROM attendance WHERE docente_id=? AND fecha=? AND hora BETWEEN ? AND ? LIMIT 1");
            $stmt->execute([$docenteId, $fecha, $inicio->format('H:i:s'), $fin->format('H:i:s')]);
        } else {
            // si no hay hora_fin, considerar ventana de 2 horas por defecto
            $winEnd = (clone $inicio)->modify('+2 hours')->format('H:i:s');
            $stmt = $db->prepare("SELECT 1 FROM attendance WHERE docente_id=? AND fecha=? AND hora BETWEEN ? AND ? LIMIT 1");
            $stmt->execute([$docenteId, $fecha, $inicio->format('H:i:s'), $winEnd]);
        }
        if ($stmt->fetchColumn()) {
            return true; // ya marcada
        }

        $estado = 'Presente';
        $inicio15 = (clone $inicio)->modify('+15 minutes');
        $inicio30 = (clone $inicio)->modify('+30 minutes');
        if ($now > $inicio15 && $now <= $inicio30) {
            $estado = 'Tarde';
            NotificationModel::create($docenteId, "Docente {$user['nombre']} llegó tarde (bloque {$bloque['hora_inicio']} - " . ($bloque['hora_fin'] ?? 'N/A') . ")");
        } elseif ($now > $inicio30) {
            // Política: si llega después de 30min, se considera Inasistencia.
            $estado = 'Ausente';
            NotificationModel::create($docenteId, "Docente {$user['nombre']} no asistió a tiempo (bloque {$bloque['hora_inicio']} - " . ($bloque['hora_fin'] ?? 'N/A') . ")");
        }

        $stmt = $db->prepare("INSERT INTO attendance(docente_id,fecha,hora,estado) VALUES(?,?,?,?)");
        return $stmt->execute([$docenteId, $fecha, $horaActual, $estado]);
    }

    // Mantener compatibilidad con llamadas existentes
    public static function getByRange($desde, $hasta) {
        return self::getByRangeFiltered($desde, $hasta, '');
    }

    public static function getByRangeFiltered($desde, $hasta, $cedula = '') {
        $db = Database::connect();
        $sql = "
            SELECT a.id, u.nombre, u.cedula AS identificacion, a.fecha, a.hora, a.estado, a.observacion
            FROM attendance a
            JOIN users u ON u.id=a.docente_id
            WHERE 1=1
        ";
        $params = [];
        if ($desde !== '') {
            $sql .= " AND a.fecha >= ?";
            $params[] = $desde;
        }
        if ($hasta !== '') {
            $sql .= " AND a.fecha <= ?";
            $params[] = $hasta;
        }
        if ($cedula !== '') {
            $sql .= " AND u.cedula LIKE ?";
            $params[] = "%{$cedula}%";
        }
        $sql .= " ORDER BY a.fecha DESC, a.hora DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function addOrUpdateObservation($attendanceId, $texto) {
        $db = Database::connect();
        $stmt = $db->prepare("UPDATE attendance SET observacion=? WHERE id=?");
        return $stmt->execute([$texto, $attendanceId]);
    }
}