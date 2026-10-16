<?php

//este archvio permite editar un documento existente en la base de datos
require_once "../../protegerr.php";
require_once "../../conexionn.php";

//solo el admin puede editar
if ((int) $_SESSION["id_rol"] !== 4) {
    http_response_code(403);
    exit("No contas con los permisos necesarios");
}

$id_documento = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);

if ($id_documento === false || $id_documento === null) {
    exit("El documento seleccionado no es válido");
}

//busca el documento
$consulta_documento = $conexion->prepare("
    SELECT *
    FROM documentos
    WHERE id_documento = :id_documento
");

$consulta_documento->execute([
    ":id_documento" => $id_documento
]);

$documento = $consulta_documento->fetch(PDO::FETCH_ASSOC);

if (!$documento) {
    http_response_code(404);
    exit("El documento no existe");
}

//acá obtiene las categorías para el menú que es desplegable
$categorias = $conexion
    ->query("
        SELECT id_categoria, nombre_categoria
        FROM categorias_documentos
        ORDER BY nombre_categoria
    ")
    ->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar documento</title>
</head>
<body>
<!-- el select muestra todas las categorías y selecciona la que tiene el documento-->
    <h1> Editar documento</h1>


    <form action="documento_actualizarr.php" method="POST">

        <!-- el id viaja oculto para saber qué registro actualizar -->
        <input
            type="hidden"
            name="id_documento"
            value="<?= (int) $documento["id_documento"] ?>"
        >

        <label for="id_categoria">Categoría:</label>
        <br>


        <select name="id_categoria" id="id_categoria" required>
            <?php foreach ($categorias as $categoria): ?>
                <option
                    value="<?= (int) $categoria["id_categoria"] ?>"
                    <?= (
                        (int) $categoria["id_categoria"] ===
                        (int) $documento["id_categoria"]
                    ) ? "selected" : "" ?>
                >
                    <?= htmlspecialchars($categoria["nombre_categoria"]) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <br><br>

        <label for="titulo"> Título:</label>
        <br>

        <input
            type="text"
            name="titulo"
            id="titulo"
            maxlength="100"
            required
            value="<?= htmlspecialchars($documento["titulo"]) ?>"
        >

        <br><br>

        <label for="descripcion"> Descripción:</label>
        <br>

        <textarea
            name="descripcion"
            id="descripcion"
            rows="5"
            cols="50"
        ><?= htmlspecialchars($documento["descripcion"] ?? "") ?></textarea>

        <br><br>

        <label for="archivo"> Archivo:</label>
        <br>

        <input
            type="text"
            name="archivo"
            id="archivo"
            maxlength="200"
            required
            value="<?= htmlspecialchars($documento["archivo"]) ?>"
        >

        <br><br>

        <label for="visibilidad"> Visibilidad:</label>
        <br>

        <select name="visibilidad" id="visibilidad" required>
            <?php
            $opciones_visibilidad = [
                "publico" => "Público",
                "medico" => "Médico",
                "enfermero" => "Enfermero"

            ];
            ?>

            <?php foreach ($opciones_visibilidad as $valor => $texto): ?>
                <option
                    value="<?= $valor ?>"
                    <?= $documento["visibilidad"] === $valor ? "selected" : "" ?>
                >
                    <?= $texto ?>
                </option>
            <?php endforeach; ?>
        </select>

        <br><br>

        <label for="estado">Estado:</label>
        <br>

        <select name="estado" id="estado" required>
            <option
                value="1"
                <?= (int) $documento["estado"] === 1 ? "selected" : "" ?>
            >
                Activo
            </option>

            <option
                value="0"
                <?= (int) $documento["estado"] === 0 ? "selected" : "" ?>
            >
                Inactivo
            </option>
        </select>

        <br><br>

        <button type="submit">Actualizar documento</button>
    </form>

    <br>

    <a href="documentoss.php">Cancelar</a>

</body>
</html>