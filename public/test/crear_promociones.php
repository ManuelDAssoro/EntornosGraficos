<?php
require_once '../../config/db.php';

echo "<h1>🎯 Agregar Promociones de Prueba</h1>";

try {
    $stmt = $pdo->query("SELECT codlocal, nombrelocal FROM locales ORDER BY codlocal");
    $locales = $stmt->fetchAll();
    
    if (empty($locales)) {
        echo "<p>❌ No hay locales en la base de datos. Crea locales primero.</p>";
        exit;
    }
    
    echo "<p>✅ Encontrados " . count($locales) . " locales</p>";
    

    $promociones_template = [
        'inicial' => [
            '10% descuento',
            '2x1 productos',
            'Envío gratis',
            '15% martes',
            '20% estudiantes'
        ],
        'medium' => [
            '25% descuento',
            '3x2 productos',
            'Envío + 20%',
            '30% 2da compra',
            'Combo 35% OFF'
        ],
        'premium' => [
            '50% descuento',
            'Compra 1 lleva 3',
            'VIP 40% OFF',
            '60% exclusivo',
            'Platino + regalo'
        ]
    ];
    
    $dias_semana_options = [
        'Lunes,Martes,Miércoles,Jueves,Viernes',
        'Sábado,Domingo',
        'Lunes,Miércoles,Viernes',
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
            
            
            if (strlen($promocion_texto) > 20) {
                $promocion_texto = substr($promocion_texto, 0, 20);
                echo "<small style='color: orange;'>⚠️ Texto truncado a 20 caracteres</small><br>";
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
            
            $stmt->execute([
                $promocion_texto,
                $fecha_desde,
                $fecha_hasta,
                $categoria,
                $dias_semana,
                $codLocal
            ]);
            
            echo "<div style='margin-left: 20px; padding: 10px; background: #f8f9fa; margin: 5px 0; border-radius: 5px;'>";
            echo "<strong>$categoria:</strong> $promocion_texto<br>";
            echo "<small>Días: " . ($dias_semana ?: 'Todos los días') . " | Válida hasta: $fecha_hasta</small>";
            echo "</div>";
            
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
        SELECT p.categoriacliente, COUNT(*) as total, l.nombrelocal
        FROM promociones p
        JOIN locales l ON p.codlocal = l.codlocal
        WHERE p.estadopromo = 'activa'
        GROUP BY p.categoriacliente, l.nombrelocal
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
            echo "<li>" . htmlspecialchars($item['nombrelocal']) . " - " . $item['total'] . " promociones</li>";
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
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>