<?php
$host = 'entornosgraficos-db-1';    // nombre del contenedor PostgreSQL
$db   = 'shopping_db';               // según docker-compose.yml
$user = 'postgres';                  // según docker-compose.yml
$pass = 'your_password';             // según docker-compose.yml
$port = '5432';

$dsn = "pgsql:host=$host;port=$port;dbname=$db;";

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error al conectar con PostgreSQL: " . $e->getMessage());
}
