<?php
require_once '../../config/db.php';

echo "<h1>🎯 Generador de Datos de Prueba Completo</h1>";
echo "<p>Este script creará una base de datos de demostración con todas las combinaciones posibles de usuarios.</p>";

try {
    // First, let's check the current state
    echo "<h2>📊 Estado Actual de la Base de Datos</h2>";
    
    $stmt = $pdo->prepare("SELECT tipoUsuario, estado, COUNT(*) as count FROM usuarios GROUP BY tipoUsuario, estado ORDER BY tipoUsuario, estado");
    $stmt->execute();
    $current_stats = $stmt->fetchAll();
    
    if (count($current_stats) > 0) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background: #f8f9fa;'><th style='padding: 8px;'>Tipo Usuario</th><th style='padding: 8px;'>Estado</th><th style='padding: 8px;'>Cantidad</th></tr>";
        foreach ($current_stats as $stat) {
            echo "<tr>";
            echo "<td style='padding: 8px;'>" . ($stat['tipoUsuario'] ?? 'NULL') . "</td>";
            echo "<td style='padding: 8px;'>" . ($stat['estado'] ?? 'NULL') . "</td>";
            echo "<td style='padding: 8px;'>" . $stat['count'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No hay usuarios en la base de datos.</p>";
    }
    
    echo "<h2>🚀 Generando Datos de Prueba...</h2>";
    
    // Define all possible user combinations
    $user_combinations = [
        // ADMINISTRADORES
        ['admin1@shopping.com', 'administrador', 'aprobado', 'Administrador Principal'],
        ['admin2@shopping.com', 'administrador', 'aprobado', 'Administrador Secundario'],
        
        // CLIENTES - Todos los estados posibles
        ['cliente1@test.com', 'cliente', 'aprobado', 'Cliente Activo 1'],
        ['cliente2@test.com', 'cliente', 'aprobado', 'Cliente Activo 2'],
        ['cliente3@test.com', 'cliente', 'aprobado', 'Cliente Activo 3'],
        ['cliente4@test.com', 'cliente', 'pendiente', 'Cliente Pendiente 1'],
        ['cliente5@test.com', 'cliente', 'pendiente', 'Cliente Pendiente 2'],
        ['cliente6@test.com', 'cliente', 'rechazado', 'Cliente Rechazado 1'],
        ['cliente7@test.com', 'cliente', 'rechazado', 'Cliente Rechazado 2'],
        
        // DUEÑOS - Todas las combinaciones posibles
        ['dueno1@test.com', 'dueno', 'pendiente', 'Dueño Pendiente 1 - Sin Local'],
        ['dueno2@test.com', 'dueno', 'pendiente', 'Dueño Pendiente 2 - Sin Local'],
        ['dueno3@test.com', 'dueno', 'pendiente', 'Dueño Pendiente 3 - Sin Local'],
        ['dueno4@test.com', 'dueno', 'aprobado', 'Dueño Aprobado 1 - Con Local'],
        ['dueno5@test.com', 'dueno', 'aprobado', 'Dueño Aprobado 2 - Con Local'],
        ['dueno6@test.com', 'dueno', 'aprobado', 'Dueño Aprobado 3 - Sin Local'],
        ['dueno7@test.com', 'dueno', 'aprobado', 'Dueño Aprobado 4 - Sin Local'],
        ['dueno8@test.com', 'dueno', 'rechazado', 'Dueño Rechazado 1'],
        ['dueno9@test.com', 'dueno', 'rechazado', 'Dueño Rechazado 2'],
        ['dueno10@test.com', 'dueno', 'rechazado', 'Dueño Rechazado 3']
    ];
    
    $users_created = 0;
    $users_existed = 0;
    
    echo "<h3>👥 Creando Usuarios...</h3>";
    
    foreach ($user_combinations as $user) {
        [$email, $tipo, $estado, $descripcion] = $user;
        
        // Check if user already exists
        $stmt = $pdo->prepare("SELECT codUsuario FROM usuarios WHERE nombreUsuario = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            echo "<p style='color: orange;'>⚠️ Ya existe: $email ($descripcion)</p>";
            $users_existed++;
        } else {
            // Create user
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombreUsuario, claveUsuario, tipoUsuario, estado) VALUES (?, ?, ?, ?)");
            $stmt->execute([$email, password_hash('demo123', PASSWORD_DEFAULT), $tipo, $estado]);
            echo "<p style='color: green;'>✅ Creado: $email ($descripcion)</p>";
            $users_created++;
        }
    }
    
    // Create some locales for testing
    echo "<h3>🏪 Creando Locales de Prueba...</h3>";
    
    $locales_data = [
        ['Tienda de Electrónicos Tech', 'Planta Baja - Local A-101', 'Electrónicos'],
        ['Restaurante La Bella Vista', 'Primer Piso - Local B-205', 'Gastronomía'],
        ['Boutique Fashion Style', 'Planta Baja - Local C-150', 'Indumentaria'],
        ['Librería El Conocimiento', 'Segundo Piso - Local D-301', 'Libros'],
        ['Café Central Express', 'Planta Baja - Local E-105', 'Gastronomía']
    ];
    
    $locales_created = 0;
    $locales_existed = 0;
    
    foreach ($locales_data as $index => $local_data) {
        [$nombre, $ubicacion, $rubro] = $local_data;
        
        // Check if local already exists
        $stmt = $pdo->prepare("SELECT codLocal FROM locales WHERE nombreLocal = ?");
        $stmt->execute([$nombre]);
        
        if ($stmt->fetch()) {
            echo "<p style='color: orange;'>⚠️ Local ya existe: $nombre</p>";
            $locales_existed++;
        } else {
            // Get a dueño aprobado to assign this local to
            $stmt = $pdo->prepare("SELECT codUsuario FROM usuarios WHERE tipoUsuario = 'dueno' AND estado = 'aprobado' AND codUsuario NOT IN (SELECT DISTINCT codUsuario FROM locales WHERE codUsuario IS NOT NULL) LIMIT 1");
            $stmt->execute();
            $dueno = $stmt->fetch();
            
            $codUsuario = null;
            if ($dueno && $index < 2) { // Only assign first 2 locales to dueños
                $codUsuario = $dueno['codUsuario'];
            }
            
            // Create local
            $stmt = $pdo->prepare("INSERT INTO locales (nombreLocal, ubicacionLocal, rubroLocal, codUsuario) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nombre, $ubicacion, $rubro, $codUsuario]);
            
            $assignedText = $codUsuario ? " (Asignado a dueño ID: $codUsuario)" : " (Sin dueño asignado)";
            echo "<p style='color: green;'>✅ Local creado: $nombre$assignedText</p>";
            $locales_created++;
        }
    }
    
    // Create some promotional data
    echo "<h3>🎉 Creando Promociones de Prueba...</h3>";
    
    // Get locales with assigned dueños for promotions
    $stmt = $pdo->prepare("SELECT codLocal, nombreLocal, codUsuario FROM locales WHERE codUsuario IS NOT NULL LIMIT 2");
    $stmt->execute();
    $locales_con_dueno = $stmt->fetchAll();
    
    $promociones_data = [
        ['Hasta 50% de descuento en todos los productos electrónicos', '2025-11-29', '2025-11-29', 'Lunes,Martes,Miércoles,Jueves,Viernes,Sábado,Domingo', 'general', 'activa'],
        ['Descuento del 20% en bebidas de 17:00 a 19:00', '2025-07-01', '2025-07-31', 'Lunes,Martes,Miércoles,Jueves,Viernes', 'general', 'activa'],
        ['Comprá 2 productos y llevate el 3ro gratis', '2025-12-01', '2025-02-28', 'Sábado,Domingo', 'general', 'activa']
    ];
    
    $promociones_created = 0;
    
    foreach ($promociones_data as $index => $promo_data) {
        if ($index < count($locales_con_dueno)) {
            $local = $locales_con_dueno[$index];
            [$textoPromo, $fechaDesde, $fechaHasta, $diasSemana, $categoriaCliente, $estadoPromo] = $promo_data;
            
            // Check if promotion already exists
            $stmt = $pdo->prepare("SELECT codPromo FROM promociones WHERE textoPromo = ? AND codLocal = ?");
            $stmt->execute([$textoPromo, $local['codLocal']]);
            
            if (!$stmt->fetch()) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO promociones (textoPromo, fechaDesdePromo, fechaHastaPromo, diasSemana, categoriaCliente, estadoPromo, codLocal) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$textoPromo, $fechaDesde, $fechaHasta, $diasSemana, $categoriaCliente, $estadoPromo, $local['codLocal']]);
                    echo "<p style='color: green;'>✅ Promoción creada: $textoPromo para " . $local['nombreLocal'] . "</p>";
                    $promociones_created++;
                } catch (Exception $e) {
                    echo "<p style='color: orange;'>⚠️ No se pudo crear promoción: " . $e->getMessage() . "</p>";
                }
            } else {
                echo "<p style='color: orange;'>⚠️ Promoción ya existe: $textoPromo</p>";
            }
        }
    }
    
    // Final statistics
    echo "<h2>📈 Resumen de Datos Creados</h2>";
    
    $stmt = $pdo->prepare("SELECT tipoUsuario, estado, COUNT(*) as count FROM usuarios GROUP BY tipoUsuario, estado ORDER BY tipoUsuario, estado");
    $stmt->execute();
    $final_stats = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr style='background: #f8f9fa;'><th style='padding: 8px;'>Tipo Usuario</th><th style='padding: 8px;'>Estado</th><th style='padding: 8px;'>Cantidad</th></tr>";
    foreach ($final_stats as $stat) {
        echo "<tr>";
        echo "<td style='padding: 8px;'>" . ($stat['tipoUsuario'] ?? 'NULL') . "</td>";
        echo "<td style='padding: 8px;'>" . ($stat['estado'] ?? 'NULL') . "</td>";
        echo "<td style='padding: 8px;'>" . $stat['count'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Count locales
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_locales, SUM(CASE WHEN codUsuario IS NOT NULL THEN 1 ELSE 0 END) as locales_asignados FROM locales");
    $stmt->execute();
    $locale_stats = $stmt->fetch();
    
    echo "<div style='background: #e3f2fd; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h3>📊 Estadísticas Finales:</h3>";
    echo "<ul>";
    echo "<li><strong>Usuarios creados:</strong> $users_created</li>";
    echo "<li><strong>Usuarios que ya existían:</strong> $users_existed</li>";
    echo "<li><strong>Locales creados:</strong> $locales_created</li>";
    echo "<li><strong>Locales que ya existían:</strong> $locales_existed</li>";
    echo "<li><strong>Promociones creadas:</strong> $promociones_created</li>";
    echo "<li><strong>Total de locales:</strong> " . $locale_stats['total_locales'] . "</li>";
    echo "<li><strong>Locales con dueño asignado:</strong> " . $locale_stats['locales_asignados'] . "</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<h2>🔑 Credenciales de Test</h2>";
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
    echo "<p><strong>Todas las cuentas de prueba tienen la contraseña:</strong> <code>demo123</code></p>";
    echo "<h4>Cuentas sugeridas para testing:</h4>";
    echo "<ul>";
    echo "<li><strong>Admin:</strong> admin1@shopping.com (Acceso completo al sistema)</li>";
    echo "<li><strong>Cliente:</strong> cliente1@test.com (Dashboard de cliente)</li>";
    echo "<li><strong>Dueño con local:</strong> dueno4@test.com (Dashboard con local asignado)</li>";
    echo "<li><strong>Dueño sin local:</strong> dueno6@test.com (Dashboard sin local - proceso de asignación)</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<h2>🎯 Casos de Prueba Disponibles</h2>";
    echo "<div style='background: #d1edff; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h4>Ahora podés probar:</h4>";
    echo "<ul>";
    echo "<li><strong>Solicitudes de Dueños:</strong> 3 solicitudes pendientes para aprobar/rechazar</li>";
    echo "<li><strong>Gestión de Dueños:</strong> Dueños aprobados y rechazados para gestionar</li>";
    echo "<li><strong>Asignación de Locales:</strong> Dueños sin locales asignados</li>";
    echo "<li><strong>Dashboard Variado:</strong> Diferentes experiencias según tipo de usuario</li>";
    echo "<li><strong>Promociones:</strong> Manejo de promociones para locales</li>";
    echo "<li><strong>Estados de Usuario:</strong> Todos los estados posibles (pendiente, aprobado, rechazado)</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<h2>🔗 Enlaces Rápidos</h2>";
    echo "<div style='display: flex; gap: 10px; flex-wrap: wrap; margin: 20px 0;'>";
    echo "<a href='../test_duenos.php' style='background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>🧪 Ver Tests</a>";
    echo "<a href='../admin_duenos.php' style='background: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>👥 Admin Dueños</a>";
    echo "<a href='../admin_locales.php' style='background: #17a2b8; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>🏪 Admin Locales</a>";
    echo "<a href='../dashboard_admin.php' style='background: #6f42c1; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>📊 Dashboard Admin</a>";
    echo "<a href='../login.php' style='background: #fd7e14; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>🔐 Login</a>";
    echo "</div>";
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745;'>";
    echo "<h3>✅ ¡Base de datos de prueba generada exitosamente!</h3>";
    echo "<p>Ya tenés todos los datos necesarios para probar completamente el sistema de gestión del shopping.</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #dc3545;'>";
    echo "<h2>❌ Error al generar datos:</h2>";
    echo "<p style='color: #721c24; font-family: monospace;'>" . $e->getMessage() . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
}
?>
