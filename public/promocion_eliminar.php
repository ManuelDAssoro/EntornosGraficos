<?php
require_once 'auth.php';
requireRole('dueno');
require_once '../config/db.php';

$codUsuario = $_SESSION['codUsuario'];
$idPromo = $_GET['id'] ?? null;

if ($idPromo) {
    $stmt = $pdo->prepare("
        SELECT p.codPromo FROM promociones p
        JOIN locales l ON p.codLocal = l.codLocal
        WHERE p.codPromo = ? AND l.codUsuario = ?
    ");
    $stmt->execute([$idPromo, $codUsuario]);
    $promo = $stmt->fetch();

    if ($promo) {
        $pdo->prepare("DELETE FROM promociones WHERE codPromo = ?")->execute([$idPromo]);
        header("Location: dueno_promociones.php?mensaje=eliminada");
        exit;
    }
}

header("Location: dueno_promociones.php");
exit;
?>


