<?php
//este archivo recibe los datos del formulario de edición
//de documento y actualiza el registro en la base de datos

require_once "../../protegerr.php";
require_once "../../conexionn.php";

//solo el admin puede actualizar documentos

if((int) $_SESSION["id_usuario"] !== 4){
    http_response_code(403);
    exit("No tienes permiso para actualizar documentos");
}

//acá obtiene los datros del formulario
$id_documento = filter_input(
    INPUT_POST,
    "id_documento",
    FILTER_VALIDATE_INT

);

$id_categoria = filter_input(
    INPUT_POST,
    "id_categoria",
    FILTER_VALIDATE_INT
);

$titulo = trim($_POST["titulo"]?? "");
$descripcion = trim($_POST["descripcion"]?? "");
$archivo= trim($_POST["archivo"]?? "");
$visibilidad = trim($_POST["visibilidad"]?? "");
$estado = filter_input(
    INPUT_POST,
    "estado",
    FILTER_VALIDATE_INT
);

$errores = [];
$visibilidades_validas = [
    "publico",
    "medico",
    "enfermero" 
];

//verifica el id del documento
if ($id_documento === false || $id_documento === null){
    $errores[] = "El documento seleccionado no es valido";
}

// "" la categoría

if ($id_categoria === false || $id_categoria === null){
    $errores[] = "La categoria no es valida";
}

//"" el titulo

if($titulo === ""){
    $errores[] = "El campo titulo no puede estar vacio";

}elseif (mb_strlen($titulo)>100) {
    $errores[] = "El titulo no puede ser mayor de 100 caracteres";

}

//"" el archvio

if($archivo === ""){
    $errores[] = "El archivo es obligatorio";
}elseif (mb_strlen($archivo) >200){
    $errores[] = "El nombre del archivo es muy largo";
}

//"" la visibilidad
if(!in_array($visibilidad, $visibilidades_validas, true)){
    $errores[]="La visibilidad no es valida";
}

//"" el estado
if($estado !==0 && $estado !==1){
    $errores[] = "El estado seleccionado no es válido";
}

if(count($errores) > 0){
    echo "<h1> No se pudo actualizar el documento </h1>";
    echo "<ul>";

    foreach ($errores as $error){
        echo "<li>" . htmlspecialchars($error) . "</li>";
    }

    echo "</ul>";
    echo '<a href="documentoss.php"> Volver </a>';
    exit;
}

//comprueba que exista la categoria puesta
$consulta_categoria = $conexion->prepare("
SELECT id_categoria
FROM categorias_documentos
WHERE id_categoria = :id_categoria");

$consulta_categoria->execute([
    ":id_categoria" => $id_categoria
]);

if (!$consulta_categoria->fetch()){
    exit("La categoria no existe");
}

//se ejecuta el update para actualizar el documento en la base de datos
$sql = " 
UPDATE documentos
SET
id_categoria = :id_categoria,
titulo = :titulo,
descripcion = :descripcion,
archivo = :archivo,
estado = :estado;
visibilidad = :visibilidad
WHERE id_documento = :id_documento";

$consulta = $conexion->prepare($sql);

$consulta->execute([
    ":id_categoria" => $id_categoria,
    ":titulo" => $titulo,
    ":descripcion" => $descripcion,
    ":archivo" => $archivo,
    ":estado" => $estado,
    ":visibilidad" => $visibilidad,
    ":id_documento" => $id_documento
]);

header("Location: documentoss.php");
exit;

