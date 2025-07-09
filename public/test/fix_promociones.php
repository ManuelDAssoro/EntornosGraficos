<?php
require_once '../../config/db.php';

try {
    $pdo->exec("ALTER TABLE promociones DROP CONSTRAINT IF EXISTS promociones_estadopromo_check");

    
    $pdo->exec("ALTER TABLE promociones ADD CONSTRAINT promociones_estadopromo_check 
               CHECK (estadopromo IN ('pendiente', 'aprobada', 'activa', 'rechazada', 'inactiva'))");
        
} catch (Exception $e) {
    echo "<p><strong>❌ Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>