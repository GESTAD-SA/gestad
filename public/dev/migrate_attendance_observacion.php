<?php
// Script de migración para asegurar que la tabla 'attendance' tenga
// - Columna 'id' como PRIMARY KEY AUTO_INCREMENT
// - Columna 'observacion' (TEXT)
// USO: http://localhost/gestad/public/dev/migrate_attendance_observacion.php

require_once __DIR__ . '/../../app/models/Database.php';

echo "Iniciando migración...\n";

try {
    $db = Database::connect();

    // Verificar si existe columna 'id'
    $hasId = false;
    $stmt = $db->query("SHOW COLUMNS FROM attendance LIKE 'id'");
    if ($stmt->fetch()) { $hasId = true; }

    if (!$hasId) {
        // Agregar 'id' y hacerlo PRIMARY KEY AUTO_INCREMENT
        // Nota: si la tabla ya tiene otra PK, puede ser necesario ajustarla manualmente.
        $db->exec("ALTER TABLE attendance ADD COLUMN id INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST");
        echo "Columna 'id' creada como PRIMARY KEY AUTO_INCREMENT.\n";
    } else {
        echo "Columna 'id' ya existe.\n";
    }

    // Verificar/crear columna 'observacion'
    $hasObs = false;
    $stmt2 = $db->query("SHOW COLUMNS FROM attendance LIKE 'observacion'");
    if ($stmt2->fetch()) { $hasObs = true; }

    if (!$hasObs) {
        $db->exec("ALTER TABLE attendance ADD COLUMN observacion TEXT NULL AFTER estado");
        echo "Columna 'observacion' creada.\n";
    } else {
        echo "Columna 'observacion' ya existe.\n";
    }

    echo "Migración finalizada correctamente.\n";
    echo "IMPORTANTE: Elimina este archivo por seguridad: public/dev/migrate_attendance_observacion.php\n";
} catch (Throwable $e) {
    http_response_code(500);
    echo "Error en migración: " . $e->getMessage();
}
