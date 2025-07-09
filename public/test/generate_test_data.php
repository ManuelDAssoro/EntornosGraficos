<?php
require_once '../../config/db.php';

echo "<h1>🎯 Generador de Datos de Prueba Completo</h1>";
echo "<p>Este script creará una base de datos de demostración con todas las combinaciones posibles de usuarios.</p>";

try {
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
    
    $user_combinations = [
        ['admin1@shopping.com', 'administrador', 'activo', 'Administrador Principal', null],
        ['admin2@shopping.com', 'administrador', 'activo', 'Administrador Secundario', null],
        
        ['cliente1@test.com', 'cliente', 'activo', 'Cliente Activo 1', 'inicial'],
        ['cliente2@test.com', 'cliente', 'activo', 'Cliente Activo 2', 'inicial'],
        ['cliente3@test.com', 'cliente', 'activo', 'Cliente Activo 3', 'inicial'],
        ['cliente4@test.com', 'cliente', 'pendiente', 'Cliente Pendiente 1', 'inicial'],
        ['cliente5@test.com', 'cliente', 'pendiente', 'Cliente Pendiente 2', 'inicial'],
        ['cliente6@test.com', 'cliente', 'rechazado', 'Cliente Rechazado 1', 'inicial'],
        ['cliente7@test.com', 'cliente', 'rechazado', 'Cliente Rechazado 2', 'inicial'],
        
        ['inicial@test.com', 'cliente', 'activo', 'Cliente Categoría Inicial', 'inicial'],
        ['medium@test.com', 'cliente', 'activo', 'Cliente Categoría Medium', 'medium'],
        ['premium@test.com', 'cliente', 'activo', 'Cliente Categoría Premium', 'premium'],
        ['cliente_medium1@test.com', 'cliente', 'activo', 'Cliente Medium 1', 'medium'],
        ['cliente_medium2@test.com', 'cliente', 'activo', 'Cliente Medium 2', 'medium'],
        ['cliente_premium1@test.com', 'cliente', 'activo', 'Cliente Premium 1', 'premium'],
        ['cliente_premium2@test.com', 'cliente', 'activo', 'Cliente Premium 2', 'premium'],
        
        ['dueno1@test.com', 'dueno', 'pendiente', 'Dueño Pendiente 1 - Sin Local', null],
        ['dueno2@test.com', 'dueno', 'pendiente', 'Dueño Pendiente 2 - Sin Local', null],
        ['dueno3@test.com', 'dueno', 'pendiente', 'Dueño Pendiente 3 - Sin Local', null],
        ['dueno4@test.com', 'dueno', 'activo', 'Dueño Activo 1 - Con Local', null],
        ['dueno5@test.com', 'dueno', 'activo', 'Dueño Activo 2 - Con Local', null],
        ['dueno6@test.com', 'dueno', 'activo', 'Dueño Activo 3 - Sin Local', null],
        ['dueno7@test.com', 'dueno', 'activo', 'Dueño Activo 4 - Sin Local', null],
        ['dueno8@test.com', 'dueno', 'rechazado', 'Dueño Rechazado 1', null],
        ['dueno9@test.com', 'dueno', 'rechazado', 'Dueño Rechazado 2', null],
        ['dueno10@test.com', 'dueno', 'rechazado', 'Dueño Rechazado 3', null]
    ];
    
    $users_created = 0;
    $users_existed = 0;
    
    echo "<h3>👥 Creando Usuarios...</h3>";
    
    foreach ($user_combinations as $user) {
        [$email, $tipo, $estado, $descripcion, $categoria] = $user;
        
        $stmt = $pdo->prepare("SELECT codUsuario FROM usuarios WHERE nombreUsuario = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            echo "<p style='color: orange;'>⚠️ Ya existe: $email ($descripcion)</p>";
            $users_existed++;
        } else {
            if ($tipo === 'cliente') {
                $stmt = $pdo->prepare("INSERT INTO usuarios (nombreUsuario, claveUsuario, tipoUsuario, estado, categoriaCliente) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$email, password_hash('demo123', PASSWORD_BCRYPT), $tipo, $estado, $categoria]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO usuarios (nombreUsuario, claveUsuario, tipoUsuario, estado) VALUES (?, ?, ?, ?)");
                $stmt->execute([$email, password_hash('demo123', PASSWORD_BCRYPT), $tipo, $estado]);
            }
            
            $categoryText = ($categoria && $tipo === 'cliente') ? " - Categoría: $categoria" : "";
            echo "<p style='color: green;'>✅ Creado: $email ($descripcion$categoryText)</p>";
            $users_created++;
        }
    }
    
    echo "<h3>🏪 Creando Locales de Prueba...</h3>";
    
    $locales_data = [
        ['Tienda de Electrónicos Tech', 'Planta Baja - Local A-101', 'Electrónicos'],
        ['Restaurante La Bella Vista', 'Primer Piso - Local B-205', 'Gastronomía'],
        ['Boutique Fashion Style', 'Planta Baja - Local C-150', 'Indumentaria'],
        ['Librería El Conocimiento', 'Segundo Piso - Local D-301', 'Libros'],
        ['Café Central Express', 'Planta Baja - Local E-105', 'Gastronomía'],
        ['Local de Prueba 1', 'Planta Baja - Local F-110', 'Varios'],
        ['Local de Prueba 2', 'Primer Piso - Local G-201', 'Varios'],
        ['Farmacia Salud Total', 'Planta Baja - Local H-120', 'Farmacia'],
        ['Zapatería Comfort Walk', 'Primer Piso - Local I-220', 'Calzado'],
        ['Perfumería Elegance', 'Segundo Piso - Local J-305', 'Perfumería']
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
            // Get a dueño activo to assign this local to
            $stmt = $pdo->prepare("SELECT codUsuario FROM usuarios WHERE tipoUsuario = 'dueno' AND estado = 'activo' AND codUsuario NOT IN (SELECT DISTINCT codUsuario FROM locales WHERE codUsuario IS NOT NULL) LIMIT 1");
            $stmt->execute();
            $dueno = $stmt->fetch();
            
            $codUsuario = null;
            if ($dueno && $index < 5) { // Assign first 5 locales to dueños
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
    
    // Create some promotional data with different categories
    echo "<h3>🎉 Creando Promociones de Prueba con Sistema de Categorías...</h3>";
    
    // Get locales with assigned dueños for promotions
    $stmt = $pdo->prepare("SELECT codLocal, nombreLocal, codUsuario FROM locales WHERE codUsuario IS NOT NULL LIMIT 10");
    $stmt->execute();
    $locales_con_dueno = $stmt->fetchAll();
    
    $fechaActual = date('Y-m-d');
    $fechaFutura = date('Y-m-d', strtotime('+30 days'));
    
    $promociones_data = [
        // PROMOCIONES PARA CATEGORÍA INICIAL
        ['20% OFF en toda la tienda - Promoción Inicial', $fechaActual, $fechaFutura, 'Lunes,Martes,Miércoles,Jueves,Viernes', 'inicial', 'activa'],
        ['2x1 en productos seleccionados - Para todos', $fechaActual, $fechaFutura, 'Sábado,Domingo', 'inicial', 'activa'],
        ['15% OFF en compras superiores a $5000', $fechaActual, $fechaFutura, 'Lunes,Martes,Miércoles,Jueves,Viernes,Sábado,Domingo', 'inicial', 'activa'],
        ['Descuento del 10% en bebidas de 17:00 a 19:00', $fechaActual, $fechaFutura, 'Lunes,Martes,Miércoles,Jueves,Viernes', 'inicial', 'activa'],
        
        // PROMOCIONES PARA CATEGORÍA MEDIUM
        ['30% OFF comprando 2 productos - Promoción Medium', $fechaActual, $fechaFutura, 'Miércoles,Jueves,Viernes,Sábado', 'medium', 'activa'],
        ['3x2 en toda la tienda - Clientes Medium', $fechaActual, $fechaFutura, 'Viernes,Sábado,Domingo', 'medium', 'activa'],
        ['25% OFF + envío gratis - Nivel Medium', $fechaActual, $fechaFutura, 'Lunes,Martes,Miércoles,Jueves,Viernes,Sábado,Domingo', 'medium', 'activa'],
        
        // PROMOCIONES PARA CATEGORÍA PREMIUM
        ['50% OFF + Regalo especial - Promoción Premium', $fechaActual, $fechaFutura, 'Viernes,Sábado,Domingo', 'premium', 'activa'],
        ['Descuento exclusivo del 40% - Solo Premium', $fechaActual, $fechaFutura, 'Lunes,Martes,Miércoles,Jueves,Viernes,Sábado,Domingo', 'premium', 'activa'],
        ['Acceso VIP + 60% OFF - Premium Elite', $fechaActual, $fechaFutura, 'Sábado,Domingo', 'premium', 'activa'],
        
        // PROMOCIONES ADICIONALES VARIADAS
        ['Happy Hour - 30% en cafés de 15:00 a 17:00', $fechaActual, $fechaFutura, 'Lunes,Martes,Miércoles,Jueves,Viernes', 'inicial', 'activa'],
        ['Combo estudiante - 20% con credencial', $fechaActual, $fechaFutura, 'Lunes,Martes,Miércoles,Jueves,Viernes', 'inicial', 'activa']
    ];
    
    $promociones_created = 0;
    
    foreach ($promociones_data as $index => $promo_data) {
        if ($index < count($locales_con_dueno)) {
            $local = $locales_con_dueno[$index % count($locales_con_dueno)];
            [$textoPromo, $fechaDesde, $fechaHasta, $diasSemana, $categoriaCliente, $estadoPromo] = $promo_data;
            
            // Check if promotion already exists
            $stmt = $pdo->prepare("SELECT codPromo FROM promociones WHERE textoPromo = ? AND codLocal = ?");
            $stmt->execute([$textoPromo, $local['codLocal']]);
            
            if (!$stmt->fetch()) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO promociones (textoPromo, fechaDesdePromo, fechaHastaPromo, diasSemana, categoriaCliente, estadoPromo, codLocal) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$textoPromo, $fechaDesde, $fechaHasta, $diasSemana, $categoriaCliente, $estadoPromo, $local['codLocal']]);
                    echo "<p style='color: green;'>✅ Promoción creada: $textoPromo para " . $local['nombreLocal'] . " (Categoría: $categoriaCliente)</p>";
                    $promociones_created++;
                } catch (Exception $e) {
                    echo "<p style='color: orange;'>⚠️ No se pudo crear promoción: " . $e->getMessage() . "</p>";
                }
            } else {
                echo "<p style='color: orange;'>⚠️ Promoción ya existe: $textoPromo</p>";
            }
        }
    }
    
    // Create some test usage records for category progression testing
    echo "<h3>📊 Creando Registros de Uso para Testing de Categorías...</h3>";
    
    $usage_records = [
        ['medium@test.com', 5], // Medium user with 5 usages (should stay medium)
        ['premium@test.com', 12], // Premium user with 12 usages (should stay premium)
        ['cliente_medium1@test.com', 3], // Should be at medium level
        ['cliente_premium1@test.com', 10] // Should be at premium level
    ];
    
    foreach ($usage_records as [$email, $count]) {
        // Get user
        $stmt = $pdo->prepare("SELECT codUsuario FROM usuarios WHERE nombreUsuario = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Get some initial category promotions for usage
            $stmt = $pdo->prepare("SELECT codPromo FROM promociones WHERE categoriaCliente = 'inicial' AND estadoPromo = 'activa' LIMIT ?");
            $stmt->execute([$count]);
            $promos = $stmt->fetchAll();
            
            $created_usage = 0;
            foreach ($promos as $promo) {
                // Check if usage already exists
                $stmt = $pdo->prepare("SELECT codUso FROM uso_promociones WHERE codUsuario = ? AND codPromo = ?");
                $stmt->execute([$user['codUsuario'], $promo['codPromo']]);
                
                if (!$stmt->fetch()) {
                    // Create usage record
                    $stmt = $pdo->prepare("INSERT INTO uso_promociones (codUsuario, codPromo, fechaUso, estado) VALUES (?, ?, NOW() - (? || ' days')::interval, 'usado')");
                    $stmt->execute([$user['codUsuario'], $promo['codPromo'], rand(1, 30)]);
                    $created_usage++;
                }
            }
            
            if ($created_usage > 0) {
                echo "<p style='color: green;'>✅ Creados $created_usage registros de uso para $email</p>";
            }
        }
    }
    
    echo "<h3>📰 Creando Novedades de Prueba con Sistema de Categorías...</h3>";
    
    try {
        // First, let's drop the table if it exists and recreate it properly
        $pdo->exec("DROP TABLE IF EXISTS novedades CASCADE");
    // Crear tabla novedades
    $sql = "CREATE TABLE novedades (
        id SERIAL PRIMARY KEY,
        titulo VARCHAR(255) NOT NULL,
        contenido TEXT NOT NULL,
        categoria_minima VARCHAR(20) DEFAULT 'unlogged',
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_publicacion DATE DEFAULT CURRENT_DATE,
        estado VARCHAR(20) DEFAULT 'activa',
        codUsuario INT REFERENCES usuarios(codUsuario)
    )";
    $pdo->exec($sql);
    echo "✅ Tabla 'novedades' creada<br>";
        
        // Get admin user ID
        $admin_id = 1;
        $stmt = $pdo->prepare("SELECT codUsuario FROM usuarios WHERE tipoUsuario = 'administrador' LIMIT 1");
        $stmt->execute();
        $admin = $stmt->fetch();
        if ($admin) {
            $admin_id = $admin['codUsuario'];
        }
        
        $novedades_data = [
            ['¡Bienvenidos al Shopping Mi Shopping!', 'Descubre todas las promociones y ofertas especiales que tenemos para ti. Navega sin necesidad de registrarte y explora nuestros locales.', 'unlogged'],
            ['Nuevo sistema de registro de clientes', 'Ahora puedes registrarte como cliente para acceder a promociones exclusivas y hacer seguimiento de tus descuentos utilizados.', 'unlogged'],
            ['Sistema de categorías para clientes registrados', 'Los clientes registrados pueden avanzar de categoría utilizando promociones: Inicial (0-2 usos), Medium (3-9 usos), y Premium (10+ usos). ¡Cada categoría desbloquea mejores ofertas!', 'inicial'],
            ['Beneficios exclusivos para clientes Medium', 'Los clientes de categoría Medium ahora tienen acceso a promociones especiales de fin de semana y descuentos adicionales en locales seleccionados.', 'medium'],
            ['Programa VIP para clientes Premium', 'Los clientes Premium disfrutan de acceso exclusivo a las mejores promociones, ofertas flash y eventos especiales del shopping.', 'premium'],
            ['Nuevos locales se suman al shopping', 'Este mes damos la bienvenida a nuevos comercios que amplían nuestra oferta gastronómica y de entretenimiento.', 'inicial'],
            ['Promociones de temporada disponibles', 'No te pierdas las ofertas especiales de temporada en indumentaria, electrónicos y mucho más.', 'unlogged'],
            ['¿Sabías que puedes buscar por código?', 'Utiliza nuestro sistema de búsqueda por código para encontrar rápidamente las promociones de tu local favorito.', 'inicial'],
            ['Eventos especiales para clientes Premium', 'Los clientes Premium tienen acceso anticipado a eventos de lanzamiento y degustaciones exclusivas.', 'premium'],
            ['Horarios extendidos en locales gastronómicos', 'Varios restaurantes y cafeterías del shopping han extendido sus horarios para ofrecerte mayor comodidad.', 'medium']
        ];
        
        $novedades_created = 0;
        $novedades_existed = 0;
        
        $stmt = $pdo->prepare("INSERT INTO novedades (titulo, contenido, categoria_minima, codUsuario) VALUES (?, ?, ?, ?)");
        
        foreach ($novedades_data as $novedad_data) {
            [$titulo, $contenido, $categoria] = $novedad_data;
            
            try {
                $stmt->execute([$titulo, $contenido, $categoria, $admin_id]);
                echo "<p style='color: green;'>✅ Novedad creada: $titulo (Categoría: $categoria)</p>";
                $novedades_created++;
            } catch (PDOException $e) {
                echo "<p style='color: orange;'>⚠️ Error creando novedad '$titulo': " . $e->getMessage() . "</p>";
            }
        }
        
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ Error configurando novedades: " . $e->getMessage() . "</p>";
        $novedades_created = 0;
        $novedades_existed = 0;
    }
    
    echo "<h2>📈 Resumen de Datos Creados</h2>";
    
    $stmt = $pdo->prepare("SELECT tipoUsuario, estado, COUNT(*) as count FROM usuarios GROUP BY tipoUsuario, estado ORDER BY tipoUsuario, estado");
    $stmt->execute();
    $final_stats = $stmt->fetchAll();
    
    echo "<h3>👥 Usuarios por Tipo y Estado:</h3>";
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
    
    // Client categories statistics
    $stmt = $pdo->prepare("SELECT categoriaCliente, COUNT(*) as count FROM usuarios WHERE tipoUsuario = 'cliente' AND categoriaCliente IS NOT NULL GROUP BY categoriaCliente ORDER BY categoriaCliente");
    $stmt->execute();
    $category_stats = $stmt->fetchAll();
    
    if (count($category_stats) > 0) {
        echo "<h3>⭐ Clientes por Categoría:</h3>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background: #f8f9fa;'><th style='padding: 8px;'>Categoría</th><th style='padding: 8px;'>Cantidad</th></tr>";
        foreach ($category_stats as $stat) {
            echo "<tr>";
            echo "<td style='padding: 8px;'>" . ucfirst($stat['categoriaCliente']) . "</td>";
            echo "<td style='padding: 8px;'>" . $stat['count'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Count locales
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_locales, SUM(CASE WHEN codUsuario IS NOT NULL THEN 1 ELSE 0 END) as locales_asignados FROM locales");
    $stmt->execute();
    $locale_stats = $stmt->fetch();
    
    // Count promotions by category
    $stmt = $pdo->prepare("SELECT categoriaCliente, COUNT(*) as count FROM promociones WHERE estadoPromo = 'activa' GROUP BY categoriaCliente ORDER BY categoriaCliente");
    $stmt->execute();
    $promo_stats = $stmt->fetchAll();
    
    // Count usage records
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_usage FROM uso_promociones");
    $stmt->execute();
    $usage_stats = $stmt->fetch();
    
    // Check if novedades table exists and has estado column
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as total_novedades FROM novedades WHERE estado = 'activa'");
        $stmt->execute();
        $novedades_stats = $stmt->fetch();
    } catch(Exception $e) {
        // Fallback if estado column doesn't exist or table doesn't exist
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) as total_novedades FROM novedades");
            $stmt->execute();
            $novedades_stats = $stmt->fetch();
        } catch(Exception $e2) {
            $novedades_stats = ['total_novedades' => 0];
        }
    }
    
    echo "<div style='background: #e3f2fd; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h3>📊 Estadísticas Finales:</h3>";
    echo "<ul>";
    echo "<li><strong>Usuarios creados:</strong> $users_created</li>";
    echo "<li><strong>Usuarios que ya existían:</strong> $users_existed</li>";
    echo "<li><strong>Locales creados:</strong> $locales_created</li>";
    echo "<li><strong>Locales que ya existían:</strong> $locales_existed</li>";
    echo "<li><strong>Promociones creadas:</strong> $promociones_created</li>";
    echo "<li><strong>Novedades creadas:</strong> $novedades_created</li>";
    echo "<li><strong>Novedades que ya existían:</strong> $novedades_existed</li>";
    echo "<li><strong>Total de locales:</strong> " . $locale_stats['total_locales'] . "</li>";
    echo "<li><strong>Locales con dueño asignado:</strong> " . $locale_stats['locales_asignados'] . "</li>";
    echo "<li><strong>Registros de uso de promociones:</strong> " . $usage_stats['total_usage'] . "</li>";
    echo "<li><strong>Novedades activas:</strong> " . $novedades_stats['total_novedades'] . "</li>";
    echo "</ul>";
    
    if (count($promo_stats) > 0) {
        echo "<h4>🎯 Promociones por Categoría:</h4>";
        echo "<ul>";
        foreach ($promo_stats as $stat) {
            echo "<li><strong>Categoría " . ucfirst($stat['categoriaCliente']) . ":</strong> " . $stat['count'] . " promociones</li>";
        }
        echo "</ul>";
    }
    echo "</div>";
    
    echo "<h2>🔑 Credenciales de Test</h2>";
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
    echo "<p><strong>Todas las cuentas de prueba tienen la contraseña:</strong> <code>demo123</code></p>";
    echo "<h4>Cuentas sugeridas para testing:</h4>";
    echo "<ul>";
    echo "<li><strong>Admin:</strong> admin1@shopping.com (Acceso completo al sistema)</li>";
    echo "<li><strong>Cliente Inicial:</strong> inicial@test.com (Solo promociones básicas)</li>";
    echo "<li><strong>Cliente Medium:</strong> medium@test.com (Promociones inicial + medium)</li>";
    echo "<li><strong>Cliente Premium:</strong> premium@test.com (Todas las promociones)</li>";
    echo "<li><strong>Dueño con local:</strong> dueno4@test.com (Dashboard con local asignado)</li>";
    echo "<li><strong>Dueño sin local:</strong> dueno6@test.com (Dashboard sin local - proceso de asignación)</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<h2>🎯 Casos de Prueba Disponibles</h2>";
    echo "<div style='background: #d1edff; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h4>Ahora podés probar:</h4>";
    echo "<ul>";
    echo "<li><strong>Sistema de Categorías:</strong> Usuarios con diferentes niveles de acceso</li>";
    echo "<li><strong>Sistema de Novedades:</strong> Noticias categorizadas por nivel de cliente</li>";
    echo "<li><strong>Navegación sin Registro:</strong> Acceso público a promociones básicas</li>";
    echo "<li><strong>Progreso de Categorías:</strong> Uso de promociones para subir de nivel</li>";
    echo "<li><strong>Filtrado por Categoría:</strong> Promociones específicas según nivel</li>";
    echo "<li><strong>Gestión de Novedades:</strong> Administradores pueden crear noticias categorizadas</li>";
    echo "<li><strong>Solicitudes de Dueños:</strong> 3 solicitudes pendientes para aprobar/rechazar</li>";
    echo "<li><strong>Gestión de Dueños:</strong> Dueños activos y rechazados para gestionar</li>";
    echo "<li><strong>Asignación de Locales:</strong> Dueños sin locales asignados</li>";
    echo "<li><strong>Dashboard Variado:</strong> Diferentes experiencias según tipo de usuario</li>";
    echo "<li><strong>Promociones Categorizada:</strong> Manejo de promociones por niveles</li>";
    echo "<li><strong>Estados de Usuario:</strong> Todos los estados posibles (pendiente, activo, rechazado)</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<h2>🔗 Enlaces Rápidos</h2>";
    echo "<div style='display: flex; gap: 10px; flex-wrap: wrap; margin: 20px 0;'>";
    echo "<a href='../index.php' style='background: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>🏠 Inicio (Acceso Público)</a>";
    echo "<a href='../buscar_descuentos.php' style='background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>🔍 Buscar Promociones</a>";
    echo "<a href='../login.php' style='background: #fd7e14; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>� Login</a>";
    echo "<a href='../admin_duenos.php' style='background: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>👥 Admin Dueños</a>";
    echo "<a href='../admin_locales.php' style='background: #17a2b8; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>🏪 Admin Locales</a>";
    echo "<a href='../dashboard_admin.php' style='background: #6f42c1; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>� Dashboard Admin</a>";
    echo "</div>";
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745;'>";
    echo "<h3>✅ ¡Base de datos de prueba con sistema de categorías generada exitosamente!</h3>";
    echo "<p>Ya tenés todos los datos necesarios para probar completamente el sistema de gestión del shopping con:</p>";
    echo "<ul>";
    echo "<li>🌟 <strong>Sistema de categorías de clientes (Inicial, Medium, Premium)</strong></li>";
    echo "<li>� <strong>Sistema de novedades categorizadas</strong></li>";
    echo "<li>�👁️ <strong>Acceso público para consultar promociones</strong></li>";
    echo "<li>🎯 <strong>Promociones específicas por categoría</strong></li>";
    echo "<li>📈 <strong>Progreso automático de categorías</strong></li>";
    echo "<li>📝 <strong>Gestión de novedades por administradores</strong></li>";
    echo "<li>👥 <strong>Gestión completa de usuarios y locales</strong></li>";
    echo "</ul>";
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
