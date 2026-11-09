<?php
// Muestra los datos de un documento (categoría, quién lo subió, etc.)
require_once __DIR__ . "/../../protegerr.php";
require_once __DIR__ . "/../../conexionn.php";

$id_documento = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if ($id_documento === false || $id_documento === null) {
    http_response_code(400);
    exit("El identificador del documento no es correcto");
}

$consulta = $conexion->prepare("
    SELECT d.id_documento, d.titulo, d.descripcion, d.archivo, d.fecha_subida,
           d.estado, d.visibilidad, c.nombre_categoria,
           CONCAT(u.nombre, ' ', u.apellido) AS usuario_cargador
    FROM documentos AS d
    INNER JOIN categorias_documentos AS c ON d.id_categoria = c.id_categoria
    INNER JOIN usuarios AS u ON d.id_usuario = u.id_usuario
    WHERE d.id_documento = :id_documento
    LIMIT 1
");
$consulta->execute([":id_documento" => $id_documento]);
$d = $consulta->fetch(PDO::FETCH_ASSOC);

// no existe, o este rol no tiene permiso para verlo
if (
    !$d ||
    (!es_admin() && (
        (int) $d["estado"] !== 1 ||
        !in_array($d["visibilidad"], VISIBILIDAD_POR_ROL[(int) $_SESSION["id_rol"]] ?? ["publico"], true)
    ))
) {
    http_response_code(404);
    exit("No se encontró el documento");
}

encabezado($d["titulo"], "documentos");
?>
<main class="contenido">
    <div class="card mx-auto" style="max-width: 640px;">
        <div class="card-body">
            <h3 class="card-title mb-3"><?= e($d["titulo"]) ?></h3>

            <p><strong>Categoría:</strong> <?= e($d["nombre_categoria"]) ?></p>
            <p><strong>Descripción:</strong><br><?= nl2br(e($d["descripcion"] ?? "")) ?></p>
            <p><strong>Subido por:</strong> <?= e($d["usuario_cargador"]) ?></p>
            <p><strong>Fecha de subida:</strong> <?= e(date("d/m/Y", strtotime($d["fecha_subida"]))) ?></p>
            <p><strong>Visibilidad:</strong> <?= e(ucfirst($d["visibilidad"])) ?></p>
            <p><strong>Estado:</strong> <?= (int) $d["estado"] === 1 ? "Activo" : "Inactivo" ?></p>

            <div class="d-flex justify-content-end gap-2">
                <a href="documentoss.php" class="btn btn-secondary">Volver al listado</a>
                <a href="descargar.php?id=<?= (int) $d["id_documento"] ?>" class="btn btn-primary">Descargar archivo</a>
            </div>
        </div>
    </div>
</main>
<?php pie(); ?>
