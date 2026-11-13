<?php
// Página a la que lleva el QR de cada documento. No pide login.
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../includes/funciones.php";
require_once __DIR__ . "/../conexion.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

$d = false;
if ($id !== false && $id !== null) {
    $q = $conexion->prepare("
        SELECT d.id_documento, d.titulo, d.descripcion, d.fecha_subida,
               d.estado, d.visibilidad, c.nombre_categoria
        FROM documentos AS d
        INNER JOIN categorias_documentos AS c ON d.id_categoria = c.id_categoria
        WHERE d.id_documento = :id AND d.visibilidad <> 'privado' LIMIT 1
    ");
    $q->execute([":id" => $id]);
    $d = $q->fetch(PDO::FETCH_ASSOC);
}

// existe, está activo y es público
$disponible = $d && (int) $d["estado"] === 1 && $d["visibilidad"] === "publico";

if (!$disponible) {
    http_response_code($d ? 403 : 404);
}

encabezado_movil($disponible ? $d["titulo"] : "Documento no disponible");
?>
<main class="container py-3" style="max-width: 720px;">
<?php if (!$disponible): ?>
    <div class="alert alert-warning">
        <?php if ($d): ?>
            Este documento es de uso interno del hospital. Si sos personal, <a href="<?= BASE_URL ?>/login.php">iniciá sesión</a>.
        <?php else: ?>
            No encontramos este documento.
        <?php endif; ?>
    </div>
    <a href="index.php" class="btn btn-hc w-100">Ver documentos disponibles</a>
<?php else: ?>
    <div class="card shadow-sm">
        <div class="card-body">
            <span class="badge text-bg-light border mb-2"><?= e($d["nombre_categoria"]) ?></span>
            <h1 class="h4"><?= e($d["titulo"]) ?></h1>

            <?php if (!empty($d["descripcion"])): ?>
                <p class="mt-3"><?= nl2br(e($d["descripcion"])) ?></p>
            <?php endif; ?>

            <p class="text-muted small mb-4">Actualizado el <?= e(date("d/m/Y", strtotime($d["fecha_subida"]))) ?></p>

            <a href="archivo.php?id=<?= (int) $d["id_documento"] ?>" class="btn btn-hc btn-lg w-100">Abrir documento</a>
        </div>
    </div>
    <a href="index.php" class="d-block text-center mt-3">Ver otros documentos</a>
<?php endif; ?>
</main>
<?php pie_movil(); ?>
