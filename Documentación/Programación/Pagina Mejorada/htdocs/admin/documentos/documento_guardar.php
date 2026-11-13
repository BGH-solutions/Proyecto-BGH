<?php
// Recibe el formulario de documento_nuevo.php, sube el archivo y guarda en la base.
require_once __DIR__ . "/../../proteger.php";
require_once __DIR__ . "/../../conexion.php";
exigir_admin();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: documento_nuevo.php");
    exit;
}
csrf_validar();

$id_categoria = filter_input(INPUT_POST, "id_categoria", FILTER_VALIDATE_INT);
$titulo = trim($_POST["titulo"] ?? "");
$descripcion = trim($_POST["descripcion"] ?? "");
$visibilidad = $_POST["visibilidad"] ?? "";
$estado = filter_input(INPUT_POST, "estado", FILTER_VALIDATE_INT);

$errores = [];

if ($id_categoria === false || $id_categoria === null) {
    $errores[] = "Seleccioná una categoría válida";
}
if ($titulo === "") {
    $errores[] = "El nombre es obligatorio";
} elseif (mb_strlen($titulo) > 100) {
    $errores[] = "El nombre no puede superar los 100 caracteres";
}
if (!is_string($visibilidad) || !isset(VISIBILIDADES_DOCUMENTOS[$visibilidad])) {
    $errores[] = "La visibilidad seleccionada no es válida";
}
if ($estado !== 0 && $estado !== 1) {
    $errores[] = "El estado debe ser activo o inactivo";
}

// la categoría debe existir
if (!$errores) {
    $c = $conexion->prepare("SELECT 1 FROM categorias_documentos WHERE id_categoria = :id");
    $c->execute([":id" => $id_categoria]);
    if (!$c->fetch()) {
        $errores[] = "La categoría seleccionada no existe";
    }
}

// sube el archivo solo si lo demás es válido
$archivo = "";
if (!$errores) {
    [$archivo, $error_subida] = procesar_subida("archivo", true);
    if ($error_subida) {
        $errores[] = $error_subida;
    }
}

if ($errores) {
    encabezado("No se pudo guardar", "documentos");
    ?>
    <main class="contenido">
        <div class="alert alert-danger">
            <h4>No se pudo guardar el documento</h4>
            <ul class="mb-0">
                <?php foreach ($errores as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
            </ul>
        </div>
        <a href="documento_nuevo.php" class="btn btn-secondary">Volver al formulario</a>
    </main>
    <?php
    pie();
    exit;
}

// id_usuario sale de la sesión, no del formulario
try {
    $consulta = $conexion->prepare("
        INSERT INTO documentos
        (id_categoria, id_usuario, titulo, descripcion, archivo, estado, visibilidad)
        VALUES
        (:id_categoria, :id_usuario, :titulo, :descripcion, :archivo, :estado, :visibilidad)
    ");
    $consulta->execute([
        ":id_categoria" => $id_categoria,
        ":id_usuario"   => $_SESSION["id_usuario"],
        ":titulo"       => $titulo,
        ":descripcion"  => $descripcion,
        ":archivo"      => $archivo,
        ":estado"       => $estado,
        ":visibilidad"  => $visibilidad,
    ]);
} catch (PDOException $ex) {
    // si falla la base, no deja el archivo huérfano
    @unlink(CARPETA_SUBIDAS . $archivo);
    throw $ex;
}

$_SESSION["flash"] = "Documento guardado correctamente.";
header("Location: documentos.php");
exit;
