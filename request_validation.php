<?php
// request_validation.php – reçoit une demande (avec ou sans PIN)

session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$sessionId = session_id();

// Nettoyer les anciennes demandes
DB::deleteOldRequests();

// Récupérer le PIN s'il est envoyé
$pin = isset($_POST['pin']) ? $_POST['pin'] : null;

// Créer ou mettre à jour la demande
$existing = DB::getRequest($sessionId);
if ($existing) {
    // Si une demande existe déjà, on met à jour le PIN (si fourni)
    if ($pin !== null) {
        // Optionnel : on pourrait aussi mettre à jour le PIN
        // Pour simplifier, on ne fait rien, ou on pourrait le modifier
        // Ici on choisit de laisser le PIN inchangé (car on veut garder le premier saisi)
    }
} else {
    // Créer une nouvelle demande
    DB::createRequest($sessionId, $pin);
}

header('Content-Type: application/json');
echo json_encode(['status' => 'success']);
exit;