<?php
//este archivo recibe los datos del formulario de documento_nuevoo.php
//y los guarda en la base de datos

require_once "../../protegerr.php";
require_once "../../conexionn.php";

//solo el admin puede guardar documentos

if ((int) $_SESSION["id_rol"] !== 4) {
    http_response_code(403);
    exit(" No contas con los permisos necesarios");
}

$id_categoria = filter_input(
    INPUT_POST,
    "id_categoria",
    FILTER_VALIDATE_INT
);

$titulo = trim($_POST["titulo"] ?? "");
$descripcion = trim($_POST["descripcion"] ?? "");
$archivo = trim($_POST["archivo"] ?? "");
$visibilidad = $_POST["visibilidad"] ?? "";
$estado = filter_input(
    INPUT_POST,
    "estado",
    FILTER_VALIDATE_INT
);

//lista de valores válidos
$visibilidades_validas = [
    "publico",
    "medico",
    "enfermero"
];

//una array donde guardaremos los errores
$errores = [];

// acá comprueba que la categoría sea un número válido
if ($id_categoria === false || $id_categoria === null) {
    $errores[] = "Selecciona una categoría válida";
}

//comprueba el título

if ($titulo === "") {
    $errores[] = "El título es obligatorio ";
} elseif (mb_strlen($titulo) > 100) {
    $errores[] = "el título no puede ser mayor a los 100 caracteres ";
}

//comprueba el archivo
if ($archivo === "") {
    $errores[] = "El nombre del archivo es obligatorio";
} elseif (mb_strlen($archivo) > 200) {
    $errores[] = "El nombre del archivo es demasiado largo";
}

//comprueba la visibilidad
if (!in_array($visibilidad, $visibilidades_validas, true)) {
    $errores[] = "La visibilidad seleccionada no es válida";
}

//comprueba que estado sea 0 o 1 (activo o inactivo)
if ($estado !== 0 && $estado !== 1) {
    $errores[] = "El estado debe ser activo o inactivo";
}

//si hay errores, los muestra y no se ejecuta el INSERT
if (count($errores) > 0) {
    echo "<h1> no se pudo guardar el documento, intenta de nuevo</h1>";
    echo "<ul>";

    foreach ($errores as $error) {
        echo "<li>" . htmlspecialchars($error) . "</li>";
    }

    echo "</ul>";
    echo '<a href="documento_nuevoo.php"> Volver al formulario </a>';
    exit;
}

//verifica que la categoría realmente esté en la base de datos.
//esto permite mostrar un mensaje más claro que el error de clave foranea
$consulta_categoria = $conexion->prepare("
    SELECT id_categoria
    FROM categorias_documentos
    WHERE id_categoria = :id_categoria
");

$consulta_categoria->execute([
    ":id_categoria" => $id_categoria
]);

if (!$consulta_categoria->fetch()) {
    exit("La categoría seleccionada no existe");
}

//prepara el INSERT.
//id_usuario sale de la sesión, no del formulario.
//gracias a esto evitamos que una persona intente fingir ser otro usuario
$sql = "
    INSERT INTO documentos
    (id_categoria,id_usuario,titulo,descripcion,archivo,estado,visibilidad)
    VALUES
    (:id_categoria,:id_usuario,:titulo,:descripcion,:archivo,:estado,:visibilidad)
";

$consulta = $conexion->prepare($sql);

//ejecuta el INSERT pasando los datos por separado
$consulta->execute([
    "id_categoria"=>$id_categoria,
    "id_usuario"=>$_SESSION["id_usuario"],
    "titulo"=>$titulo,
    "descripcion"=>$descripcion,
    "archivo"=>$archivo,
    "estado"=>$estado,
    "visibilidad"=>$visibilidad
]);

//cuando termina, vuelve al listado
header("Location: documentoss.php");
exit;