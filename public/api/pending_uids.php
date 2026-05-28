<?php
// Devuelve una lista de UIDs recientes (distintos) desde pending_uids
// Resultado: JSON { uids: ["AAAA", "BBBB", ...] }

header('Content-Type: application/json');

require_once __DIR__ . '/../../app/models/Database.php';

$limit = 20; // últimos 20 distintos

try {
    $db = Database::connect();
    // Tomar los más recientes y quedarnos con distintos
    $stmt = $db->prepare('SELECT uid FROM pending_uids ORDER BY created_at DESC LIMIT 200');
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
    // Filtrar únicos preservando orden
    $seen = [];
    $uids = [];
    foreach ($rows as $u) {
        if (!isset($seen[$u])) {
            $seen[$u] = true;
            $uids[] = $u;
            if (count($uids) >= $limit) break;
        }
    }
    echo json_encode(['uids' => $uids]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error interno']);
}
