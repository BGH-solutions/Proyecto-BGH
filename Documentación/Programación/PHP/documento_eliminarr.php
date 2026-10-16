<?php

//Este archivo se encarga de eliminar un documento de la base de datos, cosa
//que solo el administrador podrá realizar. 
//Para eso, primero se verifica que el usuario sea uno

require_once "../../protegerr.php";
require_once "../../conexionn.php";

//solo el admin puede eliminar documentos
if ((int) $_SESSION["id_rol"] !== 4) {
    http_response_code(403);
    exit("No contas con los permisos para eliminar documentos");
}

//acá obtiene el id del documento a eliminar desde la URL
$id_documento = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);

if ($id_documento === false || $id_documento === null) {
    exit("El documento seleccionado no es válido");
}

//como es con baja lógica, no se borra el registro de la base de datos, 
//solo se cambia el estado a 0
$sql = "
    UPDATE documentos
    SET estado = 0
    WHERE id_documento = :id_documento
";

$consulta = $conexion->prepare($sql);

$consulta->execute([
    ":id_documento" => $id_documento
]);

//se regresa a la lista de documentos después de eliminar el documento
header("Location: documentoss.php");
exit;