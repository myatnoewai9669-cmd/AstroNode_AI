<?php
// ============================================
// AstroNode AI — Database Connection
// File: includes.db.php
// ============================================

require_once 'C:/xampp/htdocs/as/config.php';


function getDB() {
    static $pdo = null;
    
    // Already connected — reuse
    if ($pdo) return $pdo;
    
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . 
            ";dbname="    . DB_NAME . 
            ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
        
    } catch (PDOException $e) {
        
        // JSON response (for API calls)
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header('Content-Type: application/json');
            die(json_encode([
                'error' => 'Database connection failed.',
                'detail' => DEBUG_MODE ? $e->getMessage() : null
            ]));
        }
        
        // HTML response (for pages)
        die('
        <div style="
            font-family: monospace;
            background: #0d1117;
            color: #f85149;
            padding: 24px;
            margin: 40px auto;
            max-width: 600px;
            border: 1px solid #f8514940;
            border-radius: 10px;
        ">
            <strong>⚠️ Database Error</strong><br><br>
            ' . (DEBUG_MODE ? htmlspecialchars($e->getMessage()) : 'Could not connect to MySQL.') . '
            <br><br>
            <span style="color:#8b949e;">
                Check: XAMPP MySQL running · DB name = <b>astronode</b> · 
                Import <b>sql/astronode.sql</b> in phpMyAdmin
            </span>
        </div>
        ');
    }
    
    return $pdo;
}