<?php
require_once '../../config/db.php';

$email = 'inicial@test.com';

try {
    $stmt = $pdo->prepare("SELECT codusuario, nombreusuario, categoriacliente FROM usuarios WHERE nombreusuario = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();
    
    if (!$usuario) {
        exit;
    }
    
    $stmt = $pdo->prepare("UPDATE usuarios SET categoriacliente = 'inicial' WHERE codusuario = ?");
    $stmt->execute([$usuario['codusuario']]);
    
} catch (PDOException $e) {
}