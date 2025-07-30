<?php
require_once '../config/db.php';


try {
    $stmt = $pdo->query("
        SELECT column_name 
        FROM information_schema.columns 
        WHERE table_name = 'usuarios' 
        AND column_name IN ('reset_token', 'reset_token_expiry')
    ");
    $existingColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('reset_token', $existingColumns)) {
        $pdo->exec("ALTER TABLE usuarios ADD COLUMN reset_token VARCHAR(64) NULL");
    } 
    
    if (!in_array('reset_token_expiry', $existingColumns)) {
        $pdo->exec("ALTER TABLE usuarios ADD COLUMN reset_token_expiry TIMESTAMP NULL");
    } 
    
    try {
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_usuarios_reset_token ON usuarios(reset_token)");
    } catch (Exception $e) {
        echo "<p>Error" . $e->getMessage() . "</p>";
    }
    
    
} catch (Exception $e) {
    echo "<p>Error" . $e->getMessage() . "</p>";
}
?>
