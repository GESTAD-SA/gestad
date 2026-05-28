<?php
// Endpoint para obtener el último UID no asignado (más reciente) registrado en pending_uids
// Respuesta: JSON { uid: "XXXX" } o { uid: null }

header('Content-Type: application/json');

require_once __DIR__ . '/../../app/models/Database.php';

try {
    $db = Database::connect();
    $stmt = $db->query('SELECT uid FROM pending_uids ORDER BY created_at DESC LIMIT 1');
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode(['uid' => $row ? $row['uid'] : null]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error interno']);
}
