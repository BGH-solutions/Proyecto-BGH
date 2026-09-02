<?php

// este archivo muestra los datos de un documento en especifico, 
// incluyendo la categoría y el usuario que lo cargó

require_once "proteger.php";
require_once "conexion.php";

// acá obtiene el id desde la URL.
// como documento_ver.php?id=1
$id_documento = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);

// valida que el ID sea correcto
if ($id_documento === false || $id_documento === null) {
    http_response_code(400);
    exit("El identificador del documento no es correcto");
}

// acá busca el documento y sus datos relacionados.
$sql = "
    SELECT
        d.id_documento,
        d.titulo,
        d.descripcion,
        d.archivo,
        d.fecha_subida,
        d.estado,
        d.visibilidad,
        c.nombre_categoria,
        CONCAT(u.nombre, ' ', u.apellido) AS usuario_cargador
    FROM documentos AS d
    INNER JOIN categorias_documentos AS c
        ON d.id_categoria = c.id_categoria
    INNER JOIN usuarios AS u
        ON d.id_usuario = u.id_usuario
    WHERE d.id_documento = :id_documento
    LIMIT 1
";

$consulta = $conexion->prepare($sql);

$consulta->execute([
    ":id_documento" => $id_documento
]);

$documento = $consulta->fetch(PDO::FETCH_ASSOC);

// y si no se encontró ningún documento, muestra un error 
if (!$documento) {
    http_response_code(404);
    exit("No se encontró el documento");
}
?>

// acá empieza el HTML para mostrar los datos del documento

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($documento["titulo"]) ?></title>
</head>
<body>

//htmlspecialchars() se usa para evitar que 
//se ejecute código HTML o JavaScript que pueda estar en los datos
//de la base de datos

    <p>
        <strong> Categoría:</strong>
        <?= htmlspecialchars($documento["nombre_categoria"]) ?>
    </p>

    <p>
        <strong> Descripción:</strong>
        <?= nl2br(htmlspecialchars($documento["descripcion"] ?? "")) ?>
    </p>

    <p>
        <strong> Archivo:</strong>
        <?= htmlspecialchars($documento["archivo"]) ?>
    </p>

    <p>
        <strong> Subido por:</strong>
        <?= htmlspecialchars($documento["usuario_cargador"]) ?>
    </p>

    <p>
        <strong> Fecha de subida:</strong>
        <?= htmlspecialchars($documento["fecha_subida"]) ?>
    </p>

    <p>
        // muestra el estado del documento, activo o inactivo
        <strong> Estado:</strong>
        <?php if ((int) $documento["estado"] === 1): ?>
            activo
        <?php else: ?>
            inactivo
        <?php endif; ?>
    </p>

    <p>
        <strong> Visibilidad:</strong>
        <?= htmlspecialchars($documento["visibilidad"]) ?>
    </p>

    <a href="documentos.php">Volver al listado</a>

</body>
</html>