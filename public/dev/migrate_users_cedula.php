<?php
// Script de migración para agregar la columna 'cedula' a la tabla users
// USO: http://localhost/gestad/public/dev/migrate_users_cedula.php

require_once __DIR__ . '/../../app/models/Database.php';

echo "Iniciando migración de columna 'cedula' en users...\n";

try {
    $db = Database::connect();

    $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'cedula'");
    if ($stmt->fetch()) {
        echo "La columna 'cedula' ya existe.\n";
    } else {
        $db->exec("ALTER TABLE users ADD COLUMN cedula VARCHAR(32) NULL AFTER usuario, ADD UNIQUE KEY uniq_cedula (cedula)");
        echo "Columna 'cedula' agregada y con índice único.\n";
    }

    echo "Migración completada.\n";
    echo "IMPORTANTE: Elimina este archivo por seguridad: public/dev/migrate_users_cedula.php\n";
} catch (Throwable $e) {
    http_response_code(500);
    echo "Error en migración: " . $e->getMessage();
}
