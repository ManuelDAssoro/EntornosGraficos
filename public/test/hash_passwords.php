<?php
require_once '../../config/db.php';
$usuarios = $pdo->query("SELECT codUsuario, claveUsuario FROM usuarios")->fetchAll();
foreach ($usuarios as $u) {
    // Solo si la contraseña no está hasheada (no empieza con $2y$)
    if (strpos($u['claveUsuario'], '$2y$') !== 0) {
        $hash = password_hash($u['claveUsuario'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE usuarios SET claveUsuario = ? WHERE codUsuario = ?");
        $stmt->execute([$hash, $u['codUsuario']]);
        echo "Actualizada: " . $u['nombreUsuario'] . "<br>";
    }
}
echo "Listo";
?>