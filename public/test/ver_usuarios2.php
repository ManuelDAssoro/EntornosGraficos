<?php
// este codigo es para poder ver los usuarios de la base de datos
require_once '../../config/db.php';

        $stmt = $pdo->prepare("SELECT codUsuario, claveUsuario, tipoUsuario, estado FROM usuarios WHERE nombreUsuario = ?");
        $stmt->execute([$nombreUsuario]);
        $usuario = $stmt->fetch();
        var_dump($usuario);
        print_r($usuario);

?>