<?php
require_once 'auth.php';
requireRole('administrador');
require_once '../config/db.php';

$id = $_GET['id'] ?? 0;

if (!$id) {
    header("Location: admin_novedades.php?error=" . urlencode("ID de novedad no válido"));
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM novedades WHERE id = ?");
    $stmt->execute([$id]);
    
    if ($stmt->rowCount() > 0) {
        $mensaje = "Novedad eliminada exitosamente.";
    } else {
        $mensaje = "No se encontró la novedad para eliminar.";
    }
    
    header("Location: admin_novedades.php?mensaje=" . urlencode($mensaje));
    exit;
    
} catch (PDOException $e) {
    header("Location: admin_novedades.php?error=" . urlencode("Error al eliminar la novedad: " . $e->getMessage()));
    exit;
}
?>
