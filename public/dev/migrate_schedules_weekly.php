<?php
// Script de migración para agregar columnas de horario semanal a la tabla schedules
// Uso: ejecutar en navegador o CLI una sola vez

require_once __DIR__ . '/../../app/models/Database.php';

try {
    $db = Database::connect();

    // Detectar columnas existentes
    $cols = [];
    $stmt = $db->query("SHOW COLUMNS FROM schedules");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $c) {
        $cols[$c['Field']] = true;
    }

    $alter = [];
    if (!isset($cols['dia_semana'])) {
        $alter[] = "ADD COLUMN dia_semana TINYINT NULL AFTER docente_id";
    }
    if (!isset($cols['hora_fin'])) {
        $alter[] = "ADD COLUMN hora_fin TIME NULL AFTER hora_inicio";
    }
    if (!isset($cols['salon'])) {
        $alter[] = "ADD COLUMN salon VARCHAR(64) NULL AFTER hora_fin";
    }

    if ($alter) {
        $sql = "ALTER TABLE schedules " . implode(", ", $alter);
        $db->exec($sql);
        echo "OK: columnas agregadas\n";
    } else {
        echo "OK: no hay cambios, columnas ya existen\n";
    }

    // Opcional: si existen filas legacy sin dia_semana, establecer de lunes a viernes como NULL y dejarlas para actualización manual
    echo "Listo. Actualiza cada horario con: dia_semana (1=Lun..5=Vie), hora_fin y salón.\n";
} catch (Throwable $e) {
    http_response_code(500);
    echo "Error: " . $e->getMessage();
}
