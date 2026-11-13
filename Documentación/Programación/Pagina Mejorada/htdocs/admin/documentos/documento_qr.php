<?php
// Muestra el QR de un documento para descargarlo o imprimirlo (solo administrador).
require_once __DIR__ . "/../../proteger.php";
require_once __DIR__ . "/../../conexion.php";
exigir_admin();

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if ($id === false || $id === null) {
    http_response_code(400);
    exit("Documento no válido");
}

$q = $conexion->prepare("SELECT id_documento, titulo, estado, visibilidad FROM documentos WHERE id_documento = :id");
$q->execute([":id" => $id]);
$d = $q->fetch(PDO::FETCH_ASSOC);
if (!$d) {
    http_response_code(404);
    exit("El documento no existe");
}

$url = url_publica("publico/documento.php?id=" . (int) $d["id_documento"]);
$funcionara = (int) $d["estado"] === 1 && $d["visibilidad"] === "publico";

encabezado("QR del documento", "documentos");
?>
<main class="contenido">
    <div class="card mx-auto" style="max-width: 520px;">
        <div class="card-body">
            <h4 class="text-center mb-3"><?= e($d["titulo"]) ?></h4>

            <?php if (!$funcionara): ?>
                <div class="alert alert-warning d-print-none">
                    Este documento está <strong>inactivo</strong> o no es de visibilidad <strong>Público</strong>.
                    Quien escanee el QR no podrá abrirlo. Cámbialo desde "Editar".
                </div>
            <?php endif; ?>

            <?= bloque_qr($url, "qr_" . $d["titulo"]) ?>

            <div class="text-center mt-3 d-print-none">
                <a href="documentos.php" class="btn btn-link">Volver al listado</a>
            </div>
        </div>
    </div>
</main>
<?php pie(); ?>
