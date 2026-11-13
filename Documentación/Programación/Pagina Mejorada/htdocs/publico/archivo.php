<?php
// Entrega el archivo de un documento PÚBLICO (sin login).
// Los archivos viven en uploads/documentos, que el servidor no abre directamente.
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../includes/funciones.php";
require_once __DIR__ . "/../conexion.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if ($id === false || $id === null) {
    http_response_code(400);
    exit("Documento no válido");
}

$q = $conexion->prepare("
    SELECT titulo, archivo FROM documentos
    WHERE id_documento = :id AND estado = 1 AND visibilidad = 'publico' LIMIT 1
");
$q->execute([":id" => $id]);
$d = $q->fetch(PDO::FETCH_ASSOC);

if (!$d) {
    http_response_code(404);
    exit("Documento no disponible");
}

enviar_archivo_documento($d, ["pdf", "jpg", "jpeg", "png"]);
