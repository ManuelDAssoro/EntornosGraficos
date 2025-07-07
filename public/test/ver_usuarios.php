<?php
// este codigo es para poder ver los usuarios de la base de datos
require_once '../config/db.php';
$stmt = $pdo->query("SELECT codUsuario, nombreUsuario, tipoUsuario, estado FROM usuarios");
echo "<pre>";
foreach ($stmt as $row) {
    print_r($row);
}
echo "</pre>";
?>