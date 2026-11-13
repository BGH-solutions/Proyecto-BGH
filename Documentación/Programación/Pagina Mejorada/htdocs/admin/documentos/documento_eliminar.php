<?php
// Baja lógica de un documento (estado = 0). Solo administrador y solo por POST.
require_once __DIR__ . "/../../proteger.php";
require_once __DIR__ . "/../../conexion.php";
exigir_admin();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Método no permitido");
}
csrf_validar();

$id_documento = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
if ($id_documento === false || $id_documento === null) {
    http_response_code(400);
    exit("El documento seleccionado no es válido");
}

// no se borra el registro ni el archivo: solo se marca como inactivo
$consulta = $conexion->prepare("UPDATE documentos SET estado = 0 WHERE id_documento = :id");
$consulta->execute([":id" => $id_documento]);

$_SESSION["flash"] = "Documento eliminado (quedó inactivo).";
header("Location: documentos.php");
exit;
