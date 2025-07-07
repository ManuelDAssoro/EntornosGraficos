<?php
require_once '../config/db.php';

try {
    echo "<h2>🔍 Verificando estructura actual de la tabla usuarios</h2>";
    
    $stmt = $pdo->query("SELECT column_name, data_type, is_nullable, column_default
    FROM information_schema.columns
    WHERE table_name = 'usuarios'
    ORDER BY ordinal_position");
    $columns = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr style='background: #f8f9fa;'><th style='padding: 8px;'>Campo</th><th style='padding: 8px;'>Tipo</th><th style='padding: 8px;'>Null</th><th style='padding: 8px;'>Key</th><th style='padding: 8px;'>Default</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td style='padding: 8px;'>" . $column['Field'] . "</td>";
        echo "<td style='padding: 8px;'>" . $column['Type'] . "</td>";
        echo "<td style='padding: 8px;'>" . $column['Null'] . "</td>";
        echo "<td style='padding: 8px;'>" . $column['Key'] . "</td>";
        echo "<td style='padding: 8px;'>" . ($column['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check if categoriaCliente column exists
    $hasCategoria = false;
    foreach ($columns as $column) {
        if ($column['Field'] === 'categoriaCliente') {
            $hasCategoria = true;
            break;
        }
    }
    
    if (!$hasCategoria) {
        echo "<h3>❌ Falta la columna categoriaCliente</h3>";
        echo "<p>Necesitamos agregar la columna categoriaCliente a la tabla usuarios.</p>";
        
        echo "<h3>🚀 Agregando columna categoriaCliente...</h3>";
        $stmt = $pdo->exec("ALTER TABLE usuarios ADD COLUMN categoriaCliente VARCHAR(20) CHECK (categoriaCliente IN ('inicial', 'medium', 'premium')) DEFAULT 'inicial'");
        echo "<p style='color: green;'>✅ Columna categoriaCliente agregada exitosamente.</p>";
    } else {
        echo "<h3>✅ La columna categoriaCliente ya existe</h3>";
    }
    
    // Check if we need to update existing clients
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM usuarios WHERE tipoUsuario = 'cliente' AND (categoriaCliente IS NULL OR categoriaCliente = '')");
    $result = $stmt->fetch();
    
    if ($result['count'] > 0) {
        echo "<h3>🔄 Actualizando clientes existentes...</h3>";
        $stmt = $pdo->exec("UPDATE usuarios SET categoriaCliente = 'inicial' WHERE tipoUsuario = 'cliente' AND (categoriaCliente IS NULL OR categoriaCliente = '')");
        echo "<p style='color: green;'>✅ {$stmt} clientes actualizados con categoría 'inicial'.</p>";
    }
    
    // Check uso_promociones table
    echo "<h3>🔍 Verificando tabla uso_promociones</h3>";
    $stmt = $pdo->query("SELECT to_regclass('public.uso_promociones') as exists");
    if (!$stmt->fetch()['exists']) {
        echo "<p>❌ Tabla uso_promociones no existe. Creándola...</p>";
        $createTable = "
        CREATE TABLE uso_promociones (
            id SERIAL PRIMARY KEY,
            codUsuario INT NOT NULL,
            codPromo INT NOT NULL,
            fechaUso TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            estado VARCHAR(20) DEFAULT 'usado' CHECK (estado IN ('usado', 'expirado')),
            FOREIGN KEY (codUsuario) REFERENCES usuarios(codUsuario),
            FOREIGN KEY (codPromo) REFERENCES promociones(codPromo),
            UNIQUE (codUsuario, codPromo)
        )";
        $pdo->exec($createTable);
        echo "<p style='color: green;'>✅ Tabla uso_promociones creada exitosamente.</p>";
    } else {
        echo "<p style='color: green;'>✅ Tabla uso_promociones ya existe.</p>";
    }
    
    // Show current client statistics
    echo "<h3>📊 Estadísticas actuales de clientes</h3>";
    $stmt = $pdo->query("
        SELECT 
            categoriaCliente,
            COUNT(*) as cantidad,
            STRING_AGG(nombreUsuario, ', ') as usuarios
        FROM usuarios 
        WHERE tipoUsuario = 'cliente' 
        GROUP BY categoriaCliente
    ");
    $stats = $stmt->fetchAll();
    
    if (!empty($stats)) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background: #f8f9fa;'><th style='padding: 8px;'>Categoría</th><th style='padding: 8px;'>Cantidad</th><th style='padding: 8px;'>Usuarios</th></tr>";
        foreach ($stats as $stat) {
            echo "<tr>";
            echo "<td style='padding: 8px;'>" . ucfirst($stat['categoriaCliente']) . "</td>";
            echo "<td style='padding: 8px;'>" . $stat['cantidad'] . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($stat['usuarios']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745;'>";
    echo "<h3>✅ Sistema de categorías configurado correctamente</h3>";
    echo "<p>La base de datos está lista para el sistema de categorías de clientes.</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #dc3545;'>";
    echo "<h2>❌ Error:</h2>";
    echo "<p style='color: #721c24; font-family: monospace;'>" . $e->getMessage() . "</p>";
    echo "</div>";
}
?>

<p><a href="../public/dashboard_admin.php">← Volver al Dashboard Admin</a></p>
