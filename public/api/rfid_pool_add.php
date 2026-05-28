<?php
// Endpoint para registrar UIDs leídos por el lector en una tabla de "pool".
// No interfiere con la lógica existente de asistencia.
// Método: POST
// Parametros: uid (hex en mayúsculas recomendado)
// Respuesta: JSON { status: "ok" }

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$uid = isset($_POST['uid']) ? trim($_POST['uid']) : '';
if ($uid === '') {
    http_response_code(400);
    echo json_encode(['error' => 'UID requerido']);
    exit;
}

// Validación simple: hex y longitud razonable (4 a 32 chars)
if (!preg_match('/^[0-9A-Fa-f]{4,32}$/', $uid)) {
    http_response_code(422);
    echo json_encode(['error' => 'UID inválido']);
    exit;
}

require_once __DIR__ . '/../../app/models/Database.php';

try {
    $db = Database::connect();
    $stmt = $db->prepare('INSERT INTO pending_uids(uid, created_at) VALUES(?, NOW())');
    $stmt->execute([strtoupper($uid)]);
    echo json_encode(['status' => 'ok']);
} catch (Throwable $e) {
    // Si hay duplicado exacto en el mismo segundo, simplemente retornamos ok para no fallar.
    echo json_encode(['status' => 'ok']);
}
