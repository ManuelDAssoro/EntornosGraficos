<?php
require_once '../config/db.php';

echo "<h1> Tabla USO_PROMOCIONES </h1>";

try {
    $stmt = $pdo->query("
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_name = 'uso_promociones'
    ");
    $tableExists = $stmt->fetch();

    if (!$tableExists) {
        $pdo->exec("
            CREATE TABLE uso_promociones (
                id SERIAL PRIMARY KEY,
                codusuario INTEGER NOT NULL,
                codpromo INTEGER NOT NULL,
                fecha_uso TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                estado VARCHAR(20) DEFAULT 'pendiente',
                fecha_aprobacion TIMESTAMP NULL,
                comentario_dueno TEXT NULL,
                FOREIGN KEY (codusuario) REFERENCES usuarios(codusuario),
                FOREIGN KEY (codpromo) REFERENCES promociones(codpromo),
                UNIQUE(codusuario, codpromo)
            )
        ");
        echo "<p style='color: green;'>✅ Tabla 'uso_promociones' creada exitosamente.</p>";
        
        $stmt = $pdo->query("
            SELECT table_name 
            FROM information_schema.tables 
            WHERE table_name = 'promocionesusadas'
        ");
        $oldTableExists = $stmt->fetch();
        
        if ($oldTableExists) {
            try {
                $pdo->exec("
                    INSERT INTO uso_promociones (codusuario, codpromo, fecha_uso, estado, fecha_aprobacion)
                    SELECT codusuario, codpromo, 
                           COALESCE(fechauso, CURRENT_DATE), 
                           'aceptada', 
                           CURRENT_DATE
                    FROM promocionesusadas
                    ON CONFLICT (codusuario, codpromo) DO NOTHING
                ");
                echo "<p style='color: blue;'>ℹ️ Datos migrados desde promocionesusadas a uso_promociones.</p>";
            } catch (Exception $e) {
                echo "<p style='color: orange;'>⚠️ No se pudieron migrar algunos datos: " . $e->getMessage() . "</p>";
            }
        }
    } else {
        echo "<p style='color: orange;'>⚠️ La tabla 'uso_promociones' ya existe.</p>";
        
        $stmt = $pdo->query("
            SELECT column_name, data_type, is_nullable, column_default
            FROM information_schema.columns
            WHERE table_name = 'uso_promociones'
            ORDER BY ordinal_position
        ");
        $columns = $stmt->fetchAll();
        
        echo "<h3>📋 Estructura actual de la tabla uso_promociones:</h3>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background: #f8f9fa;'><th style='padding: 8px;'>Campo</th><th style='padding: 8px;'>Tipo</th><th style='padding: 8px;'>Nulo</th><th style='padding: 8px;'>Default</th></tr>";
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($column['column_name']) . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($column['data_type']) . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($column['is_nullable']) . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($column['column_default'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM uso_promociones");
        $total = $stmt->fetchColumn();
        
        $stmt = $pdo->query("SELECT estado, COUNT(*) as cantidad FROM uso_promociones GROUP BY estado");
        $estadisticas = $stmt->fetchAll();
        
        echo "<h3>📊 Estadísticas actuales:</h3>";
        echo "<ul>";
        echo "<li><strong>Total de usos registrados:</strong> {$total}</li>";
        foreach ($estadisticas as $stat) {
            echo "<li><strong>Estado '{$stat['estado']}':</strong> {$stat['cantidad']} usos</li>";
        }
        echo "</ul>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error al obtener estadísticas: " . $e->getMessage() . "</p>";
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error durante la configuración: " . $e->getMessage() . "</p>";
}

echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3>✅ Configuración del Sistema de Aprobación completada</h3>";
echo "<p>El sistema está listo para manejar la aprobación de usos de promociones:</p>";
echo "<ul>";
echo "<li>✅ Tabla <strong>uso_promociones</strong> configurada correctamente</li>";
echo "<li>✅ Los clientes pueden usar promociones (estado: pendiente)</li>";
echo "<li>✅ Los dueños pueden aprobar/rechazar usos</li>";
echo "<li>✅ Solo los usos aprobados cuentan para categorías</li>";
echo "</ul>";
echo "<p><strong>Estados disponibles:</strong></p>";
echo "<ul>";
echo "<li><strong>pendiente:</strong> Promoción usada, esperando aprobación del dueño</li>";
echo "<li><strong>aceptada:</strong> Promoción aprobada por el dueño (cuenta para categoría)</li>";
echo "<li><strong>rechazada:</strong> Promoción rechazada por el dueño</li>";
echo "</ul>";
echo "</div>";

?>

<div style='margin: 20px 0;'>
    <h3>🔗 Enlaces útiles:</h3>
    <p><a href="dashboard_admin.php" style='color: #0066cc; text-decoration: none; padding: 8px 16px; background: #f8f9fa; border-radius: 4px; display: inline-block; margin: 4px;'>← Volver al Dashboard Admin</a></p>
    <p><a href="aprobar_promociones.php" style='color: #0066cc; text-decoration: none; padding: 8px 16px; background: #f8f9fa; border-radius: 4px; display: inline-block; margin: 4px;'>→ Panel de Aprobaciones (Dueños)</a></p>
    <p><a href="dashboard_cliente.php" style='color: #0066cc; text-decoration: none; padding: 8px 16px; background: #f8f9fa; border-radius: 4px; display: inline-block; margin: 4px;'>→ Dashboard Cliente</a></p>
</div>