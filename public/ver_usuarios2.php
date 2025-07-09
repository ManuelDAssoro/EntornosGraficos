<?php
// este codigo es para poder ver los usuarios de la base de datos
session_start();
require_once '../../config/db.php';

$errores = [];

        $stmt = $pdo->prepare("SELECT codUsuario, claveUsuario, tipoUsuario, estado FROM usuarios WHERE nombreUsuario = ?");
        $stmt->execute([$nombreUsuario]);
        $usuario = $stmt->fetch();
                echo "<pre>";
                var_dump($usuario);
                echo "</pre>";

        if ($usuario && !empty($usuario['claveUsuario']) && password_verify($claveUsuario, trim($usuario['claveUsuario']))) {
            if ($usuario['estado'] !== 'pendiente') {
                $_SESSION['usuario_id'] = $usuario['codUsuario'];
                $_SESSION['tipoUsuario'] = $usuario['tipoUsuario'];
              switch ($usuario['tipoUsuario']) {
                    case 'administrador':
                        header("Location: dashboard_admin.php");
                        break;
                    case 'cliente':
                        header("Location: dashboard_cliente.php");
                        break;
                    case 'dueno':
                        header("Location: dashboard_dueno.php");
                        break;
                    default:
                        header("Location: dashboard.php");
                        break;
                }
                exit;
            } else {
                $errores[] = "Tu cuenta aún no está activada.";
            }
        } else {
            $errores[] = "Usuario o contraseña incorrectos.";
        }


?>