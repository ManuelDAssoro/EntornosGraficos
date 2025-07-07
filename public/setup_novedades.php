<?php
require_once '../config/db.php';

echo "<h1>🛠️ Configuración de Tabla Novedades</h1>";

try {
    $sql = "CREATE TABLE IF NOT EXISTS novedades (
        id SERIAL PRIMARY KEY,
        titulo VARCHAR(255) NOT NULL,
        contenido TEXT NOT NULL,
        categoria_minima VARCHAR(20) DEFAULT 'unlogged',
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_publicacion DATE DEFAULT CURRENT_DATE,
        estado VARCHAR(20) DEFAULT 'activa',
        codUsuario INT,
        FOREIGN KEY (codUsuario) REFERENCES usuarios(codUsuario)
    )";
    
    $pdo->exec($sql);
    echo "<p style='color: green;'>✅ Tabla 'novedades' creada exitosamente.</p>";
    
    // Insert some sample news
    $sampleNews = [
        ['¡Bienvenidos al nuevo sistema de novedades!', 'Ahora podrás mantenerte informado de todas las últimas noticias del shopping. Las novedades se mostrarán según tu categoría de cliente.', 'unlogged'],
        ['Nuevo sistema de categorías para clientes', 'Los clientes ahora pueden avanzar de categoría utilizando promociones: Inicial (0-2 usos), Medium (3-9 usos), y Premium (10+ usos).', 'inicial'],
        ['Beneficios exclusivos para clientes Medium', 'Los clientes de categoría Medium ahora tienen acceso a promociones especiales y descuentos adicionales.', 'medium'],
        ['Programa VIP para clientes Premium', 'Los clientes Premium disfrutan de acceso exclusivo a las mejores promociones y ofertas especiales del shopping.', 'premium']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO novedades (titulo, contenido, categoria_minima, codUsuario) VALUES (?, ?, ?, 1)");
    
    foreach ($sampleNews as $news) {
        $stmt->execute($news);
        echo "<p style='color: blue;'>📰 Noticia de ejemplo creada: " . htmlspecialchars($news[0]) . " (Categoría: " . $news[2] . ")</p>";
    }
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h3>✅ Configuración completada exitosamente</h3>";
    echo "<p>La tabla de novedades ha sido creada y se han agregado algunas noticias de ejemplo.</p>";
    echo "<p><strong>Próximos pasos:</strong></p>";
    echo "<ul>";
    echo "<li>🔗 <a href='admin_novedades.php'>Administrar Novedades</a> - Para crear y gestionar noticias</li>";
    echo "<li>🔗 <a href='novedades.php'>Ver Novedades</a> - Para ver las noticias públicas</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
