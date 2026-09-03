<?php

// este archivo permite crear un nuevo documento en la base de datos

require_once "proteger.php";
require_once "conexion.php";

// solo el admin puede crear documentos

if ((int) $_SESSION["id_rol"] !== 4) {
    http_response_code(403);
    exit("No contas con los permisos para crear documentos.");
}

// obtiene todas las categorías para mostrarlas en un select
$sql = "
    SELECT id_categoria, nombre_categoria
    FROM categorias_documentos
    ORDER BY nombre_categoria
";

$categorias = $conexion
    ->query($sql)
    ->fetchAll(PDO::FETCH_ASSOC);
?>
// formulario para crear un nuevo documento

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title> Nuevo documento</title>
</head>
<body>

    <h1> Agregar documento</h1>

    <form action="documento_guardar.php" method="POST">

        <label for="id_categoria"> Categoría: </label>
        <br>

        <select name="id_categoria" id="id_categoria" required>
            <option value=""> Seleccioná una categoría </option>
// recorre todas las categorías y las muestra en el select

            <?php foreach ($categorias as $categoria): ?>
                <option value="<?= (int) $categoria["id_categoria"] ?>">
                    
                    <?= htmlspecialchars($categoria["nombre_categoria"]) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <br><br>

// para el título del documento

        <label for="titulo">Título:</label>
        <br>

        <input
            type="text"
            name="titulo"
            id="titulo"
            maxlength="200"
            required
        >

        <br><br>

// para la descripción del documento

        <label for="descripcion">Descripción:</label>
        <br>

        <textarea
            name="descripcion"
            id="descripcion"
            rows="5"
            cols="50"
        ></textarea>

        <br><br>

        <label for="archivo"> Nombre del archivo:</label>
        <br>

        <input
            type="text"
            name="archivo"
            id="archivo"
            maxlength="255"
            placeholder="ejemplo.pdf"
            required
        >

        <br><br>

        <label for="visibilidad"> Visibilidad: </label>
        <br>

        <select name="visibilidad" id="visibilidad" required>
            <option value="publico"> Público</option>
            <option value="medico"> Médico</option>
            <option value="enfermero"> Enfermero</option>
            <option value="personal"> Personal</option>
        </select>

        <br><br>

        <label for="estado"> Estado: </label>
        <br>

        <select name="estado" id="estado" required>
            <option value="1"> Activo </option>
            <option value="0"> Inactivo </option>
        </select>

        <br><br>

        <button type="submit"> Guardar documento </button>
    </form>

    <br>

    <a href="documentos.php">Cancelar</a>

</body>
    
</html>
