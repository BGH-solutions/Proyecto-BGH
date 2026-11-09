<?php
// Entrega el archivo de un documento comprobando antes el permiso.
// Los archivos están en uploads/documentos (bloqueado por .htaccess).
require_once __DIR__ . "/../../protegerr.php";
require_once __DIR__ . "/../../conexionn.php";

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

// basename evita rutas tipo ../../
$ruta = CARPETA_SUBIDAS . basename($d["archivo"]);
if (!is_file($ruta)) {
    http_response_code(404);
    exit("El archivo no está disponible en el servidor");
}

$ext = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
$mime = (new finfo(FILEINFO_MIME_TYPE))->file($ruta) ?: "application/octet-stream";

// nombre de descarga: el título del documento
$nombre = trim(preg_replace('/[^A-Za-z0-9._-]+/', "_", $d["titulo"]), "_") ?: "documento";

header("Content-Type: " . $mime);
header("Content-Length: " . filesize($ruta));
header('Content-Disposition: ' . ($ext === "pdf" ? "inline" : "attachment") . '; filename="' . $nombre . "." . $ext . '"');
header("X-Content-Type-Options: nosniff");
readfile($ruta);
exit;
