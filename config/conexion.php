<?php
/**
 * ARCHIVO DE CONEXIÓN A LA BASE DE DATOS - "SOLO COMPU"
 * utiliza PDO para mayor seguridad contra inyección SQL.
 */

$host = 'localhost';
$db   = 'solocompu_db';
$user = 'root'; // Usuario por defecto en XAMPP
$pass = '';     // Contraseña vacía por defecto en XAMPP
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // En producción, es mejor ocultar el mensaje exacto del error, pero para desarrollo escolar mostramos el detalle.
     die("Error de conexión a la base de datos: " . $e->getMessage());
}
?>
