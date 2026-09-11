<?php

$host = "localhost";
$usuario = "dulce_user";
$contrasena = "Dulce1234!";
$base_datos = "dulce_encanto";

$conexion = new mysqli(
    $host,
    $usuario,
    $contrasena,
    $base_datos
);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$conexion->set_charset("utf8mb4");
?>
