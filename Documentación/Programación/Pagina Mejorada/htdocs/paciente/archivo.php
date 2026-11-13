<?php
require_once __DIR__ . "/../proteger.php";
exigir_paciente();

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if ($id <= 0) {
    http_response_code(400);
    exit("Documento no válido.");
}

$consulta = $conexion->prepare("
    SELECT d.titulo, d.archivo
    FROM documentos_pacientes AS a
    JOIN documentos AS d ON d.id_documento = a.id_documento
    WHERE a.id_paciente = :paciente AND a.id_documento = :documento
      AND d.estado = 1 AND d.visibilidad IN ('publico', 'privado')
");
$consulta->execute([":paciente" => $_SESSION["id_usuario"], ":documento" => $id]);
$documento = $consulta->fetch(PDO::FETCH_ASSOC);
if (!$documento) {
    http_response_code(404);
    exit("Documento no disponible.");
}

header("Cache-Control: private, no-store");
enviar_archivo_documento($documento, ["pdf", "jpg", "jpeg", "png"]);
