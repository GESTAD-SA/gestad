<?php
// Script de migración: agrega columna 'active' a users y la deja en 1 por defecto
// USO: http://localhost/gestad/public/dev/migrate_users_active.php

require_once __DIR__ . '/../../app/models/Database.php';

echo "Iniciando migración de columna 'active' en users...\n";

try {
    $db = Database::connect();

    $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'active'");
    if ($stmt->fetch()) {
        echo "La columna 'active' ya existe.\n";
    } else {
        $db->exec("ALTER TABLE users ADD COLUMN active TINYINT(1) NOT NULL DEFAULT 1 AFTER rol");
        echo "Columna 'active' agregada con valor por defecto 1.\n";
    }

    echo "Migración completada.\n";
    echo "IMPORTANTE: Elimina este archivo por seguridad: public/dev/migrate_users_active.php\n";
} catch (Throwable $e) {
    http_response_code(500);
    echo "Error en migración: " . $e->getMessage();
}
