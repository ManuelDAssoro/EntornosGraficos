<?php
require_once '../../config/db.php';

try {
    $sql1 = "UPDATE promociones SET estadopromo = 'activa' WHERE estadopromo = 'aprobada'";
    $count1 = $pdo->exec($sql1);
    
    
    $stmt = $pdo->query("SELECT estadopromo, COUNT(*) as cantidad FROM promociones GROUP BY estadopromo");
    echo "<pre>";
    foreach ($stmt as $row) {
        echo "Estado: " . $row['estadopromo'] . " - Cantidad: " . $row['cantidad'] . "\n";
    }
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<p><strong>❌ Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>