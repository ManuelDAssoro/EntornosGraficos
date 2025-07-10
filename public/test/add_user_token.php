<?php
require_once '../../config/db.php';


try {
    $stmt = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'usuarios' AND column_name = 'token'");
    $exists = $stmt->fetch();
    
    if (!$exists) {
        // Agregar la columna token
        $pdo->exec("ALTER TABLE usuarios ADD COLUMN token VARCHAR(255) NULL");

    } 
  
} catch (Exception $e) {
    echo "<p><strong>❌ Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>