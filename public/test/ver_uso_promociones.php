<?php
require_once '../../config/db.php';

echo "<h1>🔍 Verificar estructura de tabla uso_promociones</h1>";

try {
    $stmt = $pdo->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'uso_promociones' ORDER BY ordinal_position");
    $columns = $stmt->fetchAll();
    
    if ($columns) {
        echo "<h2>✅ Columnas de la tabla uso_promociones:</h2>";
        echo "<ul>";
        foreach ($columns as $column) {
            echo "<li><strong>" . $column['column_name'] . "</strong> (" . $column['data_type'] . ")</li>";
        }
        echo "</ul>";
    } else {
        echo "<p><strong>❌ La tabla uso_promociones no existe o no tiene columnas.</strong></p>";
    }
    
    echo "<h2>📊 Datos de ejemplo (primeras 3 filas):</h2>";
    $stmt = $pdo->query("SELECT * FROM uso_promociones LIMIT 3");
    $data = $stmt->fetchAll();
    
    if ($data) {
        echo "<pre>";
        print_r($data);
        echo "</pre>";
    } else {
        echo "<p>No hay datos en la tabla.</p>";
    }
    
} catch (Exception $e) {
    echo "<p><strong>❌ Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>