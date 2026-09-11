<?php
require_once "conexion.php";

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = trim($_POST["nombre"]);
    $descripcion = trim($_POST["descripcion"]);
    $precio = floatval($_POST["precio"]);
    $stock = intval($_POST["stock"]);

    $imagen = null;

    if (
        isset($_FILES["imagen"]) &&
        $_FILES["imagen"]["error"] === UPLOAD_ERR_OK
    ) {

        $permitidos = [
            "image/jpeg" => "jpg",
            "image/png"  => "png",
            "image/webp" => "webp"
        ];

        $tipo = mime_content_type($_FILES["imagen"]["tmp_name"]);

        if (isset($permitidos[$tipo])) {

            if ($_FILES["imagen"]["size"] <= 2 * 1024 * 1024) {

                $extension = $permitidos[$tipo];

                $imagen = uniqid("producto_", true)
                        . "."
                        . $extension;

                $rutaDestino =
                    "/var/www/html/uploads/" . $imagen;

                move_uploaded_file(
                    $_FILES["imagen"]["tmp_name"],
                    $rutaDestino
                );

            } else {
                $mensaje = "La imagen debe pesar máximo 2 MB.";
            }

        } else {
            $mensaje = "Solo se permiten JPG, PNG o WEBP.";
        }
    }

    if ($mensaje === "") {

        $sql = "INSERT INTO productos
                (nombre, descripcion, imagen, precio, stock)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $conexion->prepare($sql);

        $stmt->bind_param(
            "sssdi",
            $nombre,
            $descripcion,
            $imagen,
            $precio,
            $stock
        );

        if ($stmt->execute()) {
            $mensaje = "Producto registrado correctamente ✅";
        } else {
            $mensaje = "Error al registrar el producto.";
        }

        $stmt->close();
    }
}

$resultado = $conexion->query(
    "SELECT * FROM productos ORDER BY id DESC"
);
?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Dulce Encanto</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, Helvetica, sans-serif;
    background: #fff8f6;
    color: #333;
}

header {
    background: linear-gradient(135deg, #ff8fa3, #ffb3c1);
    padding: 35px 20px;
    text-align: center;
    color: white;
}

header h1 {
    margin: 0;
    font-size: 38px;
}

header p {
    margin-top: 8px;
    font-size: 17px;
}

.contenedor {
    max-width: 1200px;
    margin: 35px auto;
    padding: 0 20px;
}

.tarjeta-formulario {
    background: white;
    border-radius: 14px;
    padding: 25px;
    margin-bottom: 35px;
    box-shadow: 0 5px 18px rgba(0,0,0,0.08);
}

.tarjeta-formulario h2 {
    margin-top: 0;
    color: #d85b78;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}

.campo {
    display: flex;
    flex-direction: column;
}

.campo-completo {
    grid-column: 1 / -1;
}

label {
    font-weight: bold;
    margin-bottom: 6px;
}

input,
textarea {
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 15px;
}

textarea {
    resize: vertical;
    min-height: 90px;
}

button {
    border: none;
    cursor: pointer;
    border-radius: 8px;
}

.btn-guardar {
    margin-top: 20px;
    padding: 13px 25px;
    background: #d85b78;
    color: white;
    font-size: 16px;
}

.btn-guardar:hover {
    background: #bd4964;
}

.mensaje {
    background: #e8f8ed;
    border-left: 5px solid #4caf50;
    padding: 14px;
    margin-bottom: 20px;
    border-radius: 6px;
}

h2.productos-titulo {
    color: #d85b78;
}

.productos {
    display: grid;
    grid-template-columns: repeat(
        auto-fill,
        minmax(240px, 1fr)
    );
    gap: 25px;
}

.producto {
    background: white;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 5px 18px rgba(0,0,0,0.08);
    transition: 0.25s;
}

.producto:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.14);
}

.producto-imagen {
    width: 100%;
    height: 210px;
    object-fit: cover;
    background: #f3f3f3;
}

.sin-imagen {
    height: 210px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f3f3f3;
    color: #888;
    font-size: 15px;
}

.producto-info {
    padding: 18px;
}

.producto-info h3 {
    margin-top: 0;
    margin-bottom: 8px;
    color: #333;
}

.descripcion {
    color: #666;
    min-height: 40px;
}

.precio {
    font-size: 23px;
    color: #d85b78;
    font-weight: bold;
    margin: 12px 0;
}

.stock {
    color: #555;
    margin-bottom: 15px;
}

.acciones {
    display: flex;
    gap: 10px;
}

.btn-editar {
    display: inline-block;
    text-decoration: none;
    padding: 9px 15px;
    background: #ffc857;
    color: #333;
    border-radius: 7px;
    font-size: 14px;
}

.btn-eliminar {
    padding: 9px 15px;
    background: #e74c3c;
    color: white;
    font-size: 14px;
}

.vacio {
    grid-column: 1 / -1;
    background: white;
    padding: 30px;
    text-align: center;
    border-radius: 12px;
    color: #777;
}

footer {
    text-align: center;
    padding: 25px;
    margin-top: 50px;
    background: #333;
    color: white;
}

@media (max-width: 700px) {

    .form-grid {
        grid-template-columns: 1fr;
    }

    .campo-completo {
        grid-column: auto;
    }

    header h1 {
        font-size: 30px;
    }

}

</style>

</head>

<body>

<header>

    <h1> Dulce Encanto</h1>

    <p>
        Sistema de gestión de productos
    </p>

</header>


<div class="contenedor">


<?php if ($mensaje !== "") { ?>

    <div class="mensaje">

        <?php echo htmlspecialchars($mensaje); ?>

    </div>

<?php } ?>


<div class="tarjeta-formulario">

    <h2>Agregar producto</h2>

    <form
        method="POST"
        enctype="multipart/form-data"
    >

        <div class="form-grid">

            <div class="campo">

                <label>Nombre</label>

                <input
                    type="text"
                    name="nombre"
                    placeholder="Ej. Torta de chocolate"
                    required
                >

            </div>


            <div class="campo">

                <label>Precio</label>

                <input
                    type="number"
                    name="precio"
                    step="0.01"
                    min="0"
                    placeholder="0.00"
                    required
                >

            </div>


            <div class="campo">

                <label>Stock</label>

                <input
                    type="number"
                    name="stock"
                    min="0"
                    placeholder="Cantidad"
                    required
                >

            </div>


            <div class="campo">

                <label>Imagen</label>

                <input
                    type="file"
                    name="imagen"
                    accept="image/jpeg,image/png,image/webp"
                >

            </div>


            <div class="campo campo-completo">

                <label>Descripción</label>

                <textarea
                    name="descripcion"
                    placeholder="Descripción del producto"
                ></textarea>

            </div>

        </div>


        <button
            type="submit"
            class="btn-guardar"
        >
            Guardar producto
        </button>

    </form>

</div>


<h2 class="productos-titulo">
    Productos registrados
</h2>


<div class="productos">


<?php if ($resultado->num_rows > 0) { ?>


<?php while ($producto = $resultado->fetch_assoc()) { ?>


<div class="producto">


<?php if (!empty($producto["imagen"])) { ?>

    <img
        class="producto-imagen"
        src="uploads/<?php
            echo htmlspecialchars(
                $producto["imagen"]
            );
        ?>"
        alt="<?php
            echo htmlspecialchars(
                $producto["nombre"]
            );
        ?>"
    >

<?php } else { ?>

    <div class="sin-imagen">

        Sin imagen

    </div>

<?php } ?>


<div class="producto-info">

    <h3>

        <?php
        echo htmlspecialchars(
            $producto["nombre"]
        );
        ?>

    </h3>


    <div class="descripcion">

        <?php
        echo htmlspecialchars(
            $producto["descripcion"]
        );
        ?>

    </div>


    <div class="precio">

        S/
        <?php
        echo number_format(
            $producto["precio"],
            2
        );
        ?>

    </div>


    <div class="stock">

        Stock:
        <?php echo $producto["stock"]; ?>

    </div>


    <div class="acciones">

        <a
            class="btn-editar"
            href="editar.php?id=<?php
                echo $producto["id"];
            ?>"
        >
             Editar
        </a>


        <form
            action="eliminar.php"
            method="POST"
            onsubmit="
                return confirm(
                    '¿Deseas eliminar este producto?'
                );
            "
        >

            <input
                type="hidden"
                name="id"
                value="<?php
                    echo $producto["id"];
                ?>"
            >

            <button
                type="submit"
                class="btn-eliminar"
            >
                 Eliminar
            </button>

        </form>

    </div>

</div>

</div>


<?php } ?>


<?php } else { ?>


<div class="vacio">

    Todavía no existen productos registrados.

</div>


<?php } ?>


</div>

</div>


<footer>

    Dulce Encanto · Aplicación desplegada en Amazon EC2

</footer>

</body>

</html>
