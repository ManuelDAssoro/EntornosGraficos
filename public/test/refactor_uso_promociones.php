<?php
require_once '../../config/db.php';

echo "<h1>🔧 Arreglar tabla uso_promociones</h1>";

try {
    $pdo->exec("ALTER TABLE uso_promociones RENAME COLUMN codcliente TO codusuario");
    echo "<p>✅ codcliente → codusuario</p>";
    
    $pdo->exec("ALTER TABLE uso_promociones RENAME COLUMN fechausopromo TO fechauso");
    echo "<p>✅ fechausopromo → fechauso</p>";
    
    $stmt = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'uso_promociones' ORDER BY ordinal_position");
    $columnas = $stmt->fetchAll();
    
    echo "<h2>📋 Columnas actuales:</h2><ul>";
    foreach ($columnas as $col) {
        echo "<li>" . $col['column_name'] . "</li>";
    }

    
} catch (Exception $e) {
    echo "<p>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>