<?php
// check_status.php – retourne le statut en JSON

session_start();
require_once 'db.php';

$sessionId = session_id();

$request = DB::getRequest($sessionId);
if (!$request) {
    // Si aucune requête, on considère que la validation est toujours en attente
    echo json_encode(['status' => 'pending']);
    exit;
}

echo json_encode([
    'status' => $request['status']
]);
exit;