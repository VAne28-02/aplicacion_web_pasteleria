<?php

require_once "conexion.php";

$id = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);

if (!$id) {
    die("ID inválido");
}


$stmt = $conexion->prepare(
    "SELECT * FROM productos WHERE id = ?"
);

$stmt->bind_param("i", $id);

$stmt->execute();

$resultado = $stmt->get_result();

$producto = $resultado->fetch_assoc();

$stmt->close();


if (!$producto) {
    die("Producto no encontrado");
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = trim($_POST["nombre"]);
    $descripcion = trim($_POST["descripcion"]);
    $precio = floatval($_POST["precio"]);
    $stock = intval($_POST["stock"]);

    $imagen = $producto["imagen"];


    if (
        isset($_FILES["imagen"]) &&
        $_FILES["imagen"]["error"] === UPLOAD_ERR_OK
    ) {

        $permitidos = [
            "image/jpeg" => "jpg",
            "image/png"  => "png",
            "image/webp" => "webp"
        ];

        $tipo = mime_content_type(
            $_FILES["imagen"]["tmp_name"]
        );

        if (isset($permitidos[$tipo])) {

            $extension = $permitidos[$tipo];

            $nuevaImagen =
                uniqid("producto_", true)
                . "."
                . $extension;

            $destino =
                "/var/www/html/uploads/"
                . $nuevaImagen;


            if (
                move_uploaded_file(
                    $_FILES["imagen"]["tmp_name"],
                    $destino
                )
            ) {

                if (
                    !empty($imagen) &&
                    file_exists(
                        "/var/www/html/uploads/"
                        . $imagen
                    )
                ) {

                    unlink(
                        "/var/www/html/uploads/"
                        . $imagen
                    );
                }

                $imagen = $nuevaImagen;
            }
        }
    }


    $sql = "UPDATE productos
            SET nombre = ?,
                descripcion = ?,
                imagen = ?,
                precio = ?,
                stock = ?
            WHERE id = ?";


    $stmt = $conexion->prepare($sql);

    $stmt->bind_param(
        "sssdii",
        $nombre,
        $descripcion,
        $imagen,
        $precio,
        $stock,
        $id
    );

    $stmt->execute();

    $stmt->close();

    header("Location: index.php");

    exit;
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Editar producto</title>

<style>

body {
    margin: 0;
    font-family: Arial, Helvetica, sans-serif;
    background: #fff8f6;
}

.contenedor {
    max-width: 650px;
    margin: 50px auto;
    padding: 20px;
}

form {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,.1);
}

h1 {
    color: #d85b78;
    text-align: center;
}

label {
    display: block;
    margin-top: 15px;
    margin-bottom: 6px;
    font-weight: bold;
}

input,
textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
}

textarea {
    min-height: 100px;
}

.imagen-actual {
    width: 180px;
    height: 150px;
    object-fit: cover;
    border-radius: 10px;
    margin: 10px 0;
}

.botones {
    margin-top: 25px;
    display: flex;
    gap: 15px;
}

button {
    border: none;
    padding: 12px 18px;
    background: #d85b78;
    color: white;
    border-radius: 8px;
    cursor: pointer;
}

a {
    padding: 12px 18px;
    background: #ddd;
    text-decoration: none;
    color: #333;
    border-radius: 8px;
}

</style>

</head>

<body>

<div class="contenedor">

<form
    method="POST"
    enctype="multipart/form-data"
>

<h1>Editar producto</h1>


<label>Nombre</label>

<input
    type="text"
    name="nombre"
    value="<?php
        echo htmlspecialchars(
            $producto["nombre"]
        );
    ?>"
    required
>


<label>Descripción</label>

<textarea
    name="descripcion"
><?php
echo htmlspecialchars(
    $producto["descripcion"]
);
?></textarea>


<label>Precio</label>

<input
    type="number"
    step="0.01"
    min="0"
    name="precio"
    value="<?php
        echo $producto["precio"];
    ?>"
    required
>


<label>Stock</label>

<input
    type="number"
    min="0"
    name="stock"
    value="<?php
        echo $producto["stock"];
    ?>"
    required
>


<label>Imagen actual</label>


<?php if (!empty($producto["imagen"])) { ?>

<img
    class="imagen-actual"
    src="uploads/<?php
        echo htmlspecialchars(
            $producto["imagen"]
        );
    ?>"
>

<?php } else { ?>

<p>Este producto no tiene imagen.</p>

<?php } ?>


<label>
    Cambiar imagen
</label>

<input
    type="file"
    name="imagen"
    accept="image/jpeg,image/png,image/webp"
>


<div class="botones">

    <button type="submit">
        Guardar cambios
    </button>

    <a href="index.php">
        Cancelar
    </a>

</div>

</form>

</div>

</body>

</html>
