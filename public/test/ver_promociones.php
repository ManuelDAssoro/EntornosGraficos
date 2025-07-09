<?php
// este codigo es para poder ver las promociones de la base de datos
require_once '../../config/db.php';
$stmt = $pdo->query("SELECT * FROM promociones");
foreach ($stmt as $row) {
    print_r($row);
    echo "<hr>";
}