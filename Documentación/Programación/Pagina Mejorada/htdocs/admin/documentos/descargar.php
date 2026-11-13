<?php
// Entrega el archivo de un documento comprobando antes el permiso.
// Los archivos están en uploads/documentos (bloqueado por .htaccess).
require_once __DIR__ . "/../../proteger.php";
require_once __DIR__ . "/../../conexion.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if ($id === false || $id === null) {
    http_response_code(400);
    exit("Documento no válido");
}

$consulta = $conexion->prepare("
    SELECT titulo, archivo, estado, visibilidad
    FROM documentos WHERE id_documento = :id LIMIT 1
");
$consulta->execute([":id" => $id]);
$d = $consulta->fetch(PDO::FETCH_ASSOC);

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

header("Cache-Control: private, no-store");
enviar_archivo_documento($d, ["pdf"]);
