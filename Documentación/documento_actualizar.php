<?php
// este archivo recibe los datos del formulario de edición de documento y
// actualiza el registro en la base de datos


require_once "proteger.php";
require_once "conexion.php";

// solo el admin puede actualizar documentos
if ((int) $_SESSION["id_rol"] !== 4) {
    http_response_code(403);
    exit("No contas con los permisos para actualizar documentos");
}

// acá obtiene los datos del formulario
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

$titulo = trim($_POST["titulo"] ?? "");
$descripcion = trim($_POST["descripcion"] ?? "");
$archivo = trim($_POST["archivo"] ?? "");
$visibilidad = $_POST["visibilidad"] ?? "";
$estado = filter_input(
    INPUT_POST,
    "estado",
    FILTER_VALIDATE_INT
);

$errores = [];

$visibilidades_validas = [
    "publico",
    "medico",
    "enfermero",
    "personal"
];

// verifica el id del documento
if ($id_documento === false || $id_documento === null) {
    $errores[] = "El documento seleccionado no es válido";
}

// verifica la categoría
if ($id_categoria === false || $id_categoria === null) {
    $errores[] = "La categoría no es válida";
}

// verifica el título
if ($titulo === "") {
    $errores[] = "El título es obligatorio";
} elseif (mb_strlen($titulo) > 100) {
    $errores[] = "El título no puede ser mayor a 100 caracteres";
}

// verifica el archivo
if ($archivo === "") {
    $errores[] = "El archivo es obligatorio";
} elseif (mb_strlen($archivo) > 222) {
    $errores[] = "El nombre del archivo es demasiado largo";
}

// verifica visibilidad
if (!in_array($visibilidad, $visibilidades_validas, true)) {
    $errores[] = "La visibilidad seleccionada no es válida";
}

// verifica estado
if ($estado !== 0 && $estado !== 1) {
    $errores[] = "El estado seleccionado no es válido";
}

// y si hay errores, los muestra y detiene el UPDATE
if (count($errores) > 0) {
    echo "<h1> No se pudo actualizar el documento </h1>";
    echo "<ul>";

    foreach ($errores as $error) {
        echo "<li>" . htmlspecialchars($error) . "</li>";
    }

    echo "</ul>";
    echo '<a href="documentos.php"> Volver </a>';
    exit;
}

// acá comprueba que exista la categoría puesta
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

// se ejecuta el update para actualizar el documento en la base de datos
$sql = "
    UPDATE documentos
    SET
        id_categoria = :id_categoria,
        titulo = :titulo,
        descripcion = :descripcion,
        archivo = :archivo,
        estado = :estado,
        visibilidad = :visibilidad
    WHERE id_documento = :id_documento
";

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

header("Location: documentos.php");
exit;