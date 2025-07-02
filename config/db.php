<?php
//cambiamos de MySQL a PostgreSQL para poder hacer el deploy en render.com
$host = 'dpg-d1i9ijur433s73a7e640-a';
$db   = 'postgresql_gc48';
$user = 'postgresql_gc48_user';
$pass = 'MGyQ538yVhNbliX8MAkdejsohq8yM7sT';
$port = '5432';

$dsn = "pgsql:host=$host;port=$port;dbname=$db;";

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

