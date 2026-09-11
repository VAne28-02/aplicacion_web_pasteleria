<?php

require_once "conexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Método no permitido");
}

$id = filter_input(
    INPUT_POST,
    "id",
    FILTER_VALIDATE_INT
);

if (!$id) {
    die("ID inválido");
}


// Buscar la imagen
$stmt = $conexion->prepare(
    "SELECT imagen FROM productos WHERE id = ?"
);

$stmt->bind_param("i", $id);

$stmt->execute();

$resultado = $stmt->get_result();

$producto = $resultado->fetch_assoc();

$stmt->close();


// Eliminar producto
$stmt = $conexion->prepare(
    "DELETE FROM productos WHERE id = ?"
);

$stmt->bind_param("i", $id);

$stmt->execute();

$stmt->close();


// Eliminar archivo de imagen
if (
    $producto &&
    !empty($producto["imagen"])
) {

    $archivo =
        "/var/www/html/uploads/"
        . $producto["imagen"];

    if (file_exists($archivo)) {
        unlink($archivo);
    }
}


header("Location: index.php");
exit;

?>
