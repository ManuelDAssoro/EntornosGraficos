<?php
require_once '../../config/db.php';

try {
    echo "<h1>🔧 Arreglando Constraints de Base de Datos</h1>";
    
    $pdo->exec("ALTER TABLE usuarios DROP CONSTRAINT IF EXISTS usuarios_categoriacliente_check");
    echo "✅ Constraint anterior eliminado<br>";

    $pdo->exec("ALTER TABLE usuarios ADD CONSTRAINT usuarios_categoriacliente_check 
                CHECK (categoriaCliente IN ('Inicial', 'Medium', 'Premium', 'inicial', 'medium', 'premium'))");
    echo "✅ Nuevo constraint agregado (acepta mayúsculas y minúsculas)<br>";

    $pdo->exec("ALTER TABLE usuarios DROP CONSTRAINT IF EXISTS usuarios_tipousuario_check");
    $pdo->exec("ALTER TABLE usuarios ADD CONSTRAINT usuarios_tipousuario_check 
                CHECK (tipoUsuario IN ('administrador', 'dueño de local', 'cliente', 'Administrador', 'Dueño de local', 'Cliente'))");
    echo "✅ Constraint de tipoUsuario actualizado<br>";

    $pdo->exec("ALTER TABLE promociones DROP CONSTRAINT IF EXISTS promociones_categoriacliente_check");
    $pdo->exec("ALTER TABLE promociones ADD CONSTRAINT promociones_categoriacliente_check 
                CHECK (categoriaCliente IN ('Inicial', 'Medium', 'Premium', 'inicial', 'medium', 'premium'))");
    echo "✅ Constraint de promociones actualizado<br>";

    $pdo->exec("ALTER TABLE novedades DROP CONSTRAINT IF EXISTS novedades_tipousuario_check");
    $pdo->exec("ALTER TABLE novedades ADD CONSTRAINT novedades_tipousuario_check 
                CHECK (tipoUsuario IN ('administrador', 'dueño de local', 'cliente', 'Administrador', 'Dueño de local', 'Cliente'))");
    echo "✅ Constraint de novedades actualizado<br>";

    $pdo->exec("ALTER TABLE uso_promociones DROP CONSTRAINT IF EXISTS uso_promociones_estado_check");
    $pdo->exec("ALTER TABLE uso_promociones ADD CONSTRAINT uso_promociones_estado_check 
                CHECK (estado IN ('enviada', 'aceptada', 'rechazada', 'Enviada', 'Aceptada', 'Rechazada'))");
    echo "✅ Constraint de uso_promociones actualizado<br>";

    $pdo->exec("ALTER TABLE promociones DROP CONSTRAINT IF EXISTS promociones_estadopromo_check");
    $pdo->exec("ALTER TABLE promociones ADD CONSTRAINT promociones_estadopromo_check 
                CHECK (estadoPromo IN ('pendiente', 'aprobada', 'denegada', 'Pendiente', 'Aprobada', 'Denegada'))");
    echo "✅ Constraint de estado promociones actualizado<br>";
    
    echo "<h2>✅ Constraints Actualizados</h2>";
    echo "<p><strong>⚠️ IMPORTANTE:</strong> Eliminar este archivo después de usarlo.</p>";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>