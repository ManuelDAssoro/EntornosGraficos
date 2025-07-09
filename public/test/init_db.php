<?php
require_once '../../config/db.php';

try {
    echo "<h1>🔧 Inicializando Base de Datos Shopping</h1>";
    
    // Eliminar tablas existentes si existen (para empezar limpio)
    echo "<h2>🧹 Limpiando base de datos...</h2>";
    $pdo->exec("DROP TABLE IF EXISTS uso_promociones CASCADE");
    $pdo->exec("DROP TABLE IF EXISTS promociones CASCADE");
    $pdo->exec("DROP TABLE IF EXISTS novedades CASCADE");
    $pdo->exec("DROP TABLE IF EXISTS locales CASCADE");
    $pdo->exec("DROP TABLE IF EXISTS usuarios CASCADE");
    echo "✅ Tablas anteriores eliminadas<br>";
    
    echo "<h2>📋 Creando Tablas...</h2>";
    
    // Crear tabla usuarios
    $sql = "CREATE TABLE usuarios (
        codUsuario SERIAL PRIMARY KEY,
        nombreUsuario VARCHAR(100) NOT NULL,
        claveUsuario VARCHAR(255) NOT NULL,
        tipoUsuario VARCHAR(20) NOT NULL CHECK (tipoUsuario IN ('administrador', 'dueno', 'cliente')),
        categoriaCliente VARCHAR(20) CHECK (categoriaCliente IN ('inicial', 'medium', 'premium')),
        estado VARCHAR(20) DEFAULT 'pendiente'
    )";
    $pdo->exec($sql);
    echo "✅ Tabla 'usuarios' creada<br>";
    
    // Crear tabla locales
    $sql = "CREATE TABLE locales (
        codLocal SERIAL PRIMARY KEY,
        nombreLocal VARCHAR(100) NOT NULL,
        ubicacionLocal VARCHAR(50),
        rubroLocal VARCHAR(20),
        codUsuario INTEGER REFERENCES usuarios(codUsuario)
    )";
    $pdo->exec($sql);
    echo "✅ Tabla 'locales' creada<br>";
    
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
    
    // Crear tabla promociones
    $sql = "CREATE TABLE promociones (
        codPromo SERIAL PRIMARY KEY,
        textoPromo VARCHAR(200) NOT NULL,
        fechaDesdePromo DATE NOT NULL,
        fechaHastaPromo DATE NOT NULL,
        categoriaCliente VARCHAR(20) NOT NULL CHECK (categoriaCliente IN ('inicial', 'medium', 'premium')),
        diasSemana VARCHAR(20) NOT NULL,
        estadoPromo VARCHAR(20) DEFAULT 'pendiente' CHECK (estadoPromo IN ('pendiente', 'activa', 'denegada')),
        codLocal INTEGER REFERENCES locales(codLocal)
    )";
    $pdo->exec($sql);
    echo "✅ Tabla 'promociones' creada<br>";
    
    // Crear tabla uso_promociones
    $sql = "CREATE TABLE uso_promociones (
        codCliente INTEGER NOT NULL REFERENCES usuarios(codUsuario),
        codPromo INTEGER NOT NULL REFERENCES promociones(codPromo),
        fechaUsoPromo DATE NOT NULL,
        estado VARCHAR(20) DEFAULT 'enviada' CHECK (estado IN ('enviada', 'aceptada', 'rechazada')),
        PRIMARY KEY (codCliente, codPromo)
    )";
    $pdo->exec($sql);
    echo "✅ Tabla 'uso_promociones' creada<br>";
    
    echo "<h2>🚀 Insertando Datos de Prueba...</h2>";
    
    // Insertar usuario administrador
    $stmt = $pdo->prepare("INSERT INTO usuarios (nombreUsuario, claveUsuario, tipoUsuario, categoriaCliente, estado) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        'admin@admin.com',
        '$2y$10$MiHi21t44MX93RaiktkrTORUMkMpG.gX2dGb7YcOBJgNNVqMI.GYi', // password: admin123
        'administrador',
        null,
        'aprobado'
    ]);
    echo "✅ Usuario administrador creado<br>";
    
    // Insertar más usuarios de prueba
    $usuarios = [
        ['dueño@local1.com', password_hash('local123', PASSWORD_BCRYPT), 'dueno', null, 'aprobado'],
        ['cliente1@email.com', password_hash('cliente123', PASSWORD_BCRYPT), 'cliente', 'inicial', 'aprobado'],
        ['cliente2@email.com', password_hash('cliente456', PASSWORD_BCRYPT), 'cliente', 'medium', 'aprobado'],
        ['cliente3@email.com', password_hash('cliente789', PASSWORD_BCRYPT), 'cliente', 'premium', 'aprobado']
    ];
    
    foreach ($usuarios as $usuario) {
        $stmt->execute($usuario);
    }
    echo "✅ Usuarios adicionales creados<br>";
    
    // Insertar local de prueba
    $stmt = $pdo->prepare("INSERT INTO locales (nombreLocal, ubicacionLocal, rubroLocal, codUsuario) VALUES (?, ?, ?, ?)");
    $stmt->execute(['jorge', 'rosario', 'bronce', 2]); // usuario 2 es el dueno
    echo "✅ Local de prueba creado<br>";
    
    // Insertar algunas promociones de prueba
    $stmt = $pdo->prepare("INSERT INTO promociones (textoPromo, fechaDesdePromo, fechaHastaPromo, categoriaCliente, diasSemana, estadoPromo, codLocal) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $promociones = [
        ['20% descuento en toda la tienda', '2025-07-01', '2025-07-31', 'inicial', 'Lunes,Martes', 'activa', 1],
        ['Oferta especial clientes Medium', '2025-07-01', '2025-08-15', 'medium', 'Miércoles,Jueves', 'activa', 1],
        ['Descuento VIP clientes Premium', '2025-07-01', '2025-12-31', 'premium', 'Viernes,Sábado', 'pendiente', 1]
    ];
    // Cambiar estado de promociones aprobadas a activas
    $sql = "UPDATE promociones SET estadoPromo = 'activa' WHERE estadoPromo = 'aprobada'";
    $count = $pdo->exec($sql);

    foreach ($promociones as $promo) {
        $stmt->execute($promo);
    }
    
    echo "✅ Promociones de prueba creadas<br>";
    
    // Insertar algunas novedades de prueba
    $stmt = $pdo->prepare("INSERT INTO novedades (titulo, contenido, categoria_minima, fecha_publicacion, estado, codUsuario) VALUES (?, ?, ?, ?, ?, ?)");
    $novedades = [
        ['Nuevas funcionalidades disponibles', 'Descubre las nuevas funciones del sistema.', 'unlogged', '2025-07-01', 'activa', 1],
        ['Promociones de verano disponibles', 'Aprovecha las promociones de verano.', 'cliente', '2025-07-01', 'activa', 1],
        ['Herramientas de gestión mejoradas', 'Mejoras en la gestión para dueños.', 'dueno', '2025-07-01', 'activa', 1]
    ];

    foreach ($novedades as $novedad) {
        $stmt->execute($novedad);
    }
    echo "✅ Novedades de prueba creadas<br>";
    
    echo "<h2>📊 Estadísticas Finales</h2>";
    
    // Mostrar resumen de datos
    $tablas = ['usuarios', 'locales', 'promociones', 'novedades', 'uso_promociones'];
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr style='background: #f8f9fa;'><th style='padding: 8px;'>Tabla</th><th style='padding: 8px;'>Registros</th></tr>";
    
    foreach ($tablas as $tabla) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM $tabla");
        $count = $stmt->fetch()['count'];
        echo "<tr>";
        echo "<td style='padding: 8px;'>" . ucfirst($tabla) . "</td>";
        echo "<td style='padding: 8px;'>" . $count . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>✅ Inicialización Completada</h2>";
    echo "<p><strong>Usuarios creados:</strong></p>";
    echo "<ul>";
    echo "<li>admin@admin.com / admin123 (Administrador)</li>";
    echo "<li>dueño@local1.com / local123 (dueno)</li>";
    echo "<li>cliente1@email.com / cliente123 (Cliente Inicial)</li>";
    echo "<li>cliente2@email.com / cliente456 (Cliente Medium)</li>";
    echo "<li>cliente3@email.com / cliente789 (Cliente Premium)</li>";
    echo "</ul>";
    
    echo "<p><strong>⚠️ IMPORTANTE:</strong> Elimina este archivo después de usarlo por seguridad.</p>";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
    echo "<br>Código de error: " . $e->getCode();
}
?>