<?php
// Cron para marcar inasistencias automáticamente cuando han pasado 30 minutos del inicio del bloque
// Programar cada 5-10 minutos. Ejemplo en Windows Task Scheduler o cron en Linux.

require_once __DIR__ . '/../../app/models/Database.php';
require_once __DIR__ . '/../../app/models/NotificationModel.php';

try {
    $db = Database::connect();

    $now = new DateTime('now');
    $w = (int)$now->format('N'); // 1=Lun..7=Dom
    if ($w < 1 || $w > 5) {
        echo "Fuera de L-V, no se procesa.\n";
        exit;
    }

    $fecha = $now->format('Y-m-d');
    $hora = $now->format('H:i:s');

    // Buscar bloques de hoy cuyo inicio +30min < ahora
    $stmt = $db->prepare("SELECT s.*, u.nombre AS docente_nombre
                           FROM schedules s
                           JOIN users u ON u.id = s.docente_id
                           WHERE s.dia_semana = ? AND ADDTIME(s.hora_inicio, '00:30:00') < ?");
    $stmt->execute([$w, $hora]);
    $bloques = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $insert = $db->prepare("INSERT INTO attendance(docente_id, fecha, hora, estado) VALUES(?,?,?,?)");
    $check  = $db->prepare("SELECT 1 FROM attendance WHERE docente_id=? AND fecha=? AND hora BETWEEN ? AND ? LIMIT 1");

    $count = 0;
    foreach ($bloques as $b) {
        $docenteId = (int)$b['docente_id'];
        $inicio = DateTime::createFromFormat('H:i:s', $b['hora_inicio']);
        $fin = isset($b['hora_fin']) && $b['hora_fin'] ? DateTime::createFromFormat('H:i:s', $b['hora_fin']) : (clone $inicio)->modify('+2 hours');

        // Verificar si ya existe asistencia/tardía/ausente registrada en el bloque de hoy
        $check->execute([$docenteId, $fecha, $inicio->format('H:i:s'), $fin->format('H:i:s')]);
        if ($check->fetchColumn()) {
            continue; // ya hay registro
        }

        // Marcar inasistencia
        $insert->execute([$docenteId, $fecha, $inicio->format('H:i:s'), 'Ausente']);
        NotificationModel::create($docenteId, "Docente {$b['docente_nombre']} no asistió a tiempo (bloque {$b['hora_inicio']} - " . ($b['hora_fin'] ?? 'N/A') . ")");
        $count++;
    }

    echo "OK: Inasistencias marcadas: {$count}\n";
} catch (Throwable $e) {
    http_response_code(500);
    echo "Error: " . $e->getMessage();
}
