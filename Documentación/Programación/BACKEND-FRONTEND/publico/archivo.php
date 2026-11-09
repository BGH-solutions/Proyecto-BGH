<?php
// Entrega el archivo de un documento PÚBLICO (sin login).
// Los archivos viven en uploads/documentos, que el servidor no abre directamente.
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../includes/funciones.php";
require_once __DIR__ . "/../conexionn.php";

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

$ruta = CARPETA_SUBIDAS . basename($d["archivo"]);   // basename evita rutas tipo ../../
if (!is_file($ruta)) {
    http_response_code(404);
    exit("El archivo no está disponible en el servidor");
}

$ext = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
$mime = (new finfo(FILEINFO_MIME_TYPE))->file($ruta) ?: "application/octet-stream";
$nombre = trim(preg_replace('/[^A-Za-z0-9._-]+/', "_", $d["titulo"]), "_") ?: "documento";

header("Content-Type: " . $mime);
header("Content-Length: " . filesize($ruta));
// PDF e imágenes se abren en el navegador del celular; Word se descarga
header("Content-Disposition: " . (in_array($ext, ["pdf", "jpg", "jpeg", "png"], true) ? "inline" : "attachment")
    . '; filename="' . $nombre . "." . $ext . '"');
header("X-Content-Type-Options: nosniff");
readfile($ruta);
exit;
