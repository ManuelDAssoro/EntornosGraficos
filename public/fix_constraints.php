<?php
require_once '../config/db.php';

echo "<h1>🔧 Fix Check Constraints - USO_PROMOCIONES</h1>";

try {
    echo "<h3>📋 Verificando constraints existentes:</h3>";
    
    $stmt = $pdo->query("
        SELECT tc.constraint_name, tc.constraint_type
        FROM information_schema.table_constraints tc
        WHERE tc.table_name = 'uso_promociones'
        ORDER BY tc.constraint_type, tc.constraint_name
    ");
    $constraints = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr style='background: #f8f9fa;'><th style='padding: 8px;'>Constraint</th><th style='padding: 8px;'>Tipo</th></tr>";
    foreach ($constraints as $constraint) {
        echo "<tr>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($constraint['constraint_name']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($constraint['constraint_type']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>🔄 Eliminando todos los check constraints:</h3>";
    
    $stmt = $pdo->query("
        SELECT constraint_name 
        FROM information_schema.table_constraints 
        WHERE table_name = 'uso_promociones' 
        AND constraint_type = 'CHECK'
    ");
    $checkConstraints = $stmt->fetchAll();
    
    foreach ($checkConstraints as $constraint) {
        try {
            $constraintName = $constraint['constraint_name'];
            $pdo->exec("ALTER TABLE uso_promociones DROP CONSTRAINT \"$constraintName\"");
            echo "<p style='color: green;'>✅ Constraint '$constraintName' eliminado exitosamente.</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Error eliminando '$constraintName': " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<h3>➕ Creando nuevo check constraint:</h3>";
    
    try {
        $pdo->exec("ALTER TABLE uso_promociones ADD CONSTRAINT uso_promociones_estado_check CHECK (estado IN ('pendiente', 'aceptada', 'rechazada'))");
        echo "<p style='color: green;'>✅ Nuevo check constraint creado exitosamente.</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error creando constraint: " . $e->getMessage() . "</p>";
    }
    
    echo "<h3>🧪 Probando inserción de prueba:</h3>";
    
    try {
        $pdo->beginTransaction();
        
        $pdo->exec("
            INSERT INTO uso_promociones (codusuario, codpromo, fecha_uso, estado) 
            VALUES (999, 999, CURRENT_TIMESTAMP, 'pendiente')
        ");
        
        $pdo->exec("DELETE FROM uso_promociones WHERE codusuario = 999 AND codpromo = 999");
        
        $pdo->commit();
        echo "<p style='color: green;'>✅ Prueba exitosa: El constraint permite 'pendiente'.</p>";
        
    } catch (Exception $e) {
        $pdo->rollback();
        echo "<p style='color: red;'>❌ Prueba fallida: " . $e->getMessage() . "</p>";
    }
    
    echo "<h3>📋 Constraints finales:</h3>";
    
    $stmt = $pdo->query("
        SELECT tc.constraint_name, tc.constraint_type
        FROM information_schema.table_constraints tc
        WHERE tc.table_name = 'uso_promociones'
        ORDER BY tc.constraint_type, tc.constraint_name
    ");
    $finalConstraints = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr style='background: #f8f9fa;'><th style='padding: 8px;'>Constraint</th><th style='padding: 8px;'>Tipo</th></tr>";
    foreach ($finalConstraints as $constraint) {
        echo "<tr>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($constraint['constraint_name']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($constraint['constraint_type']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error general: " . $e->getMessage() . "</p>";
}

echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3>✅ Fix completado</h3>";
echo "<p>Ahora intenta usar una promoción nuevamente.</p>";
echo "</div>";

?>

<div style='margin: 20px 0;'>
    <p><a href="usar_promocion.php?id=5" style='color: #0066cc; text-decoration: none; padding: 8px 16px; background: #f8f9fa; border-radius: 4px; display: inline-block; margin: 4px;'>→ Probar Usar Promoción</a></p>
    <p><a href="dashboard_cliente.php" style='color: #0066cc; text-decoration: none; padding: 8px 16px; background: #f8f9fa; border-radius: 4px; display: inline-block; margin: 4px;'>← Volver al Dashboard</a></p>
</div>
