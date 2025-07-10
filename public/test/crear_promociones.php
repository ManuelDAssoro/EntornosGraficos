<?php
require_once '../../config/db.php';

echo "<h1>🎯 Agregar Promociones de Prueba</h1>";

try {
    echo "<h2>🔍 Verificando estructura de tabla promociones...</h2>";
    $stmt = $pdo->query("
        SELECT column_name, data_type, character_maximum_length 
        FROM information_schema.columns 
        WHERE table_name = 'promociones' 
        ORDER BY ordinal_position
    ");
    $columnas = $stmt->fetchAll();
    
    echo "<table border='1' style='margin: 10px 0; border-collapse: collapse;'>";
    echo "<tr style='background: #f0f0f0;'><th>Columna</th><th>Tipo</th><th>Longitud Máxima</th></tr>";
    foreach ($columnas as $col) {
        echo "<tr>";
        echo "<td style='padding: 5px;'>" . $col['column_name'] . "</td>";
        echo "<td style='padding: 5px;'>" . $col['data_type'] . "</td>";
        echo "<td style='padding: 5px;'>" . ($col['character_maximum_length'] ?? 'N/A') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    $stmt = $pdo->query("SELECT codlocal, nombrelocal FROM locales ORDER BY codlocal");
    $locales = $stmt->fetchAll();
    
    if (empty($locales)) {
        echo "<p>❌ No hay locales en la base de datos. Crea locales primero.</p>";
        exit;
    }
    
    echo "<p>✅ Encontrados " . count($locales) . " locales</p>";
    
    
    $promociones_template = [
        'inicial' => [
            '10% OFF',           
            '2x1',               
            'Envío gratis',      
            '15% martes',        
            '20% estudiante'     
        ],
        'medium' => [
            '25% OFF',          
            '3x2',               
            'Envío + 20%',       
            '30% 2da',           
            'Combo 35%'          
        ],
        'premium' => [
            '50% OFF',           
            'Llevá 3 por 1',     
            'VIP 40%',           
            '60% exclusivo',     
            'Platino'            
        ]
    ];
    
    $dias_semana_options = [
        'Lunes,Martes',
        'Sábado,Domingo', 
        'Lunes,Viernes',
        'Martes,Jueves',
        '' 
    ];
    
    $pdo->beginTransaction();
    
    $contador = 0;
    
    foreach ($locales as $index => $local) {
        $codLocal = $local['codlocal'];
        $nombreLocal = $local['nombrelocal'];
        
        echo "<h3>🏪 Local: " . htmlspecialchars($nombreLocal) . "</h3>";
        
        foreach ($promociones_template as $categoria => $promociones_lista) {
            $promocion_texto = $promociones_lista[$index % count($promociones_lista)];
            $dias_semana = $dias_semana_options[$contador % count($dias_semana_options)];
            
            $fecha_desde = date('Y-m-d');
            $fecha_hasta = date('Y-m-d', strtotime('+2 months'));
            
            
            $longitud = strlen($promocion_texto);
            echo "<small>Texto: '$promocion_texto' (longitud: $longitud)</small><br>";
            
            if ($longitud > 20) {
                $promocion_texto = substr($promocion_texto, 0, 20);
                echo "<small style='color: red;'>❌ Texto truncado de $longitud a 20 caracteres: '$promocion_texto'</small><br>";
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO promociones (
                    textopromo, 
                    fechadesdepromo, 
                    fechahastapromo, 
                    categoriacliente, 
                    diassemana, 
                    estadopromo, 
                    codlocal
                ) VALUES (?, ?, ?, ?, ?, 'activa', ?)
            ");
            
            $resultado = $stmt->execute([
                $promocion_texto,
                $fecha_desde,
                $fecha_hasta,
                $categoria,
                $dias_semana,
                $codLocal
            ]);
            
            if ($resultado) {
                echo "<div style='margin-left: 20px; padding: 10px; background: #d4edda; margin: 5px 0; border-radius: 5px;'>";
                echo "✅ <strong>$categoria:</strong> '$promocion_texto'<br>";
                echo "<small>Días: " . ($dias_semana ?: 'Todos los días') . " | Válida hasta: $fecha_hasta</small>";
                echo "</div>";
            } else {
                echo "<div style='margin-left: 20px; padding: 10px; background: #f8d7da; margin: 5px 0; border-radius: 5px;'>";
                echo "❌ Error insertando promoción '$promocion_texto'";
                echo "</div>";
            }
            
            $contador++;
        }
    }
    
    $pdo->commit();
    
    echo "<div style='background: #d4edda; padding: 20px; margin: 20px 0; border-radius: 5px;'>";
    echo "<h2>🎉 ¡Promociones agregadas exitosamente!</h2>";
    echo "<p><strong>Total de promociones creadas:</strong> " . ($contador) . "</p>";
    echo "<p><strong>Distribución:</strong></p>";
    echo "<ul>";
    echo "<li>📢 <strong>Inicial:</strong> " . count($locales) . " promociones</li>";
    echo "<li>⭐ <strong>Medium:</strong> " . count($locales) . " promociones</li>";
    echo "<li>👑 <strong>Premium:</strong> " . count($locales) . " promociones</li>";
    echo "</ul>";
    echo "</div>";
    
   
    echo "<h2>📊 Resumen de promociones por categoría</h2>";
    
    $stmt = $pdo->query("
        SELECT p.categoriacliente, COUNT(*) as total, l.nombrelocal, p.textopromo
        FROM promociones p
        JOIN locales l ON p.codlocal = l.codlocal
        WHERE p.estadopromo = 'activa'
        ORDER BY p.categoriacliente, l.nombrelocal
    ");
    $resumen = $stmt->fetchAll();
    
    $categorias = [];
    foreach ($resumen as $item) {
        $categorias[$item['categoriacliente']][] = $item;
    }
    
    foreach ($categorias as $categoria => $items) {
        echo "<h3>🏷️ Categoría: " . ucfirst($categoria) . "</h3>";
        echo "<ul>";
        foreach ($items as $item) {
            echo "<li>" . htmlspecialchars($item['nombrelocal']) . " - '" . htmlspecialchars($item['textopromo']) . "'</li>";
        }
        echo "</ul>";
    }
    
    echo "<div style='background: #fff3cd; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
    echo "<h3>🧪 Para probar el sistema:</h3>";
    echo "<ol>";
    echo "<li><strong>Usuario Inicial:</strong> Puede ver promociones 'inicial'</li>";
    echo "<li><strong>Usuario Medium:</strong> Puede ver promociones 'inicial' y 'medium'</li>";
    echo "<li><strong>Usuario Premium:</strong> Puede ver todas las promociones</li>";
    echo "<li><strong>Usuario no registrado:</strong> Puede ver todas pero no usar</li>";
    echo "</ol>";
    echo "<p><a href='../buscar_descuentos.php' style='background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>🔍 Ver promociones en el sitio</a></p>";
    echo "</div>";
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "<div style='background: #f8d7da; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
    echo "<h3>❌ Error al crear promociones:</h3>";
    echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Código:</strong> " . $e->getCode() . "</p>";
    echo "</div>";
}
?>