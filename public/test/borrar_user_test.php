<?php
require_once '../../config/db.php';

$emailToDelete = 'manueldassoro@gmail.com';

echo "<h1>🗑️ Eliminar Usuario de Prueba</h1>";
echo "<p>Eliminando usuario con email: <strong>$emailToDelete</strong></p>";

try {
    $stmt = $pdo->prepare("SELECT codusuario, nombreusuario, tipousuario FROM usuarios WHERE nombreusuario = ?");
    $stmt->execute([$emailToDelete]);
    $usuario = $stmt->fetch();
    
    if ($usuario) {        
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE nombreusuario = ?");
        $result = $stmt->execute([$emailToDelete]);
        
        
    }     
} catch (Exception $e) {
    echo "<p><strong>❌ Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>