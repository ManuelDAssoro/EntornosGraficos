<?php

require_once '../../config/db.php';
// recupero las distintas categorias de las promociones, es para ver si hay un error 
$stmt = $pdo->query("SELECT DISTINCT categoriaCliente FROM promociones");
echo "<pre>";
foreach ($stmt as $row) {
    print_r($row);
}
echo "</pre>";
// este codigo es para poder ver las promociones de la base de datos
$stmt = $pdo->query("SELECT * FROM promociones");
foreach ($stmt as $row) {
    print_r($row);
    echo "<hr>";
}