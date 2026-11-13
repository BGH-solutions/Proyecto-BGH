<?php
// Recibe el formulario de edición y actualiza el registro.
require_once __DIR__ . "/../../proteger.php";
require_once __DIR__ . "/../../conexion.php";
exigir_admin();   // corregido: antes comparaba id_usuario en lugar de id_rol

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: documentos.php");
    exit;
}
csrf_validar();

$id_documento = filter_input(INPUT_POST, "id_documento", FILTER_VALIDATE_INT);
$id_categoria = filter_input(INPUT_POST, "id_categoria", FILTER_VALIDATE_INT);
$titulo = trim($_POST["titulo"] ?? "");
$descripcion = trim($_POST["descripcion"] ?? "");
$visibilidad = $_POST["visibilidad"] ?? "";
$estado = filter_input(INPUT_POST, "estado", FILTER_VALIDATE_INT);

$errores = [];

if ($id_documento === false || $id_documento === null) {
    $errores[] = "El documento seleccionado no es válido";
}
if ($id_categoria === false || $id_categoria === null) {
    $errores[] = "La categoría no es válida";
}
if ($titulo === "") {
    $errores[] = "El nombre no puede estar vacío";
} elseif (mb_strlen($titulo) > 100) {
    $errores[] = "El nombre no puede superar los 100 caracteres";
}
if (!is_string($visibilidad) || !isset(VISIBILIDADES_DOCUMENTOS[$visibilidad])) {
    $errores[] = "La visibilidad no es válida";
}
if ($estado !== 0 && $estado !== 1) {
    $errores[] = "El estado seleccionado no es válido";
}

$actual = null;
if (!$errores) {
    $c = $conexion->prepare("SELECT 1 FROM categorias_documentos WHERE id_categoria = :id");
    $c->execute([":id" => $id_categoria]);
    if (!$c->fetch()) {
        $errores[] = "La categoría no existe";
    }

    $q = $conexion->prepare("SELECT archivo, visibilidad FROM documentos WHERE id_documento = :id");
    $q->execute([":id" => $id_documento]);
    $actual = $q->fetch(PDO::FETCH_ASSOC);
    if (!$actual) {
        $errores[] = "El documento no existe";
    } elseif ($actual["visibilidad"] === "privado" && $visibilidad !== "privado") {
        $errores[] = "Un documento privado conserva su visibilidad.";
    }
}

// archivo nuevo (opcional): si no se sube, se conserva el anterior
$archivo_nuevo = "";
if (!$errores) {
    [$archivo_nuevo, $error_subida] = procesar_subida("archivo", false);
    if ($error_subida) {
        $errores[] = $error_subida;
    }
}

if ($errores) {
    encabezado("No se pudo actualizar", "documentos");
    ?>
    <main class="contenido">
        <div class="alert alert-danger">
            <h4>No se pudo actualizar el documento</h4>
            <ul class="mb-0">
                <?php foreach ($errores as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
            </ul>
        </div>
        <a href="documentos.php" class="btn btn-secondary">Volver</a>
    </main>
    <?php
    pie();
    exit;
}

// corregido: había un ";" donde iba una coma (estado = :estado;)
$sql = "
    UPDATE documentos
    SET id_categoria = :id_categoria,
        titulo       = :titulo,
        descripcion  = :descripcion,
        archivo      = :archivo,
        estado       = :estado,
        visibilidad  = CASE WHEN visibilidad = 'privado' THEN 'privado' ELSE :visibilidad END
    WHERE id_documento = :id_documento
";

try {
    $conexion->prepare($sql)->execute([
        ":id_categoria" => $id_categoria,
        ":titulo"       => $titulo,
        ":descripcion"  => $descripcion,
        ":archivo"      => $archivo_nuevo !== "" ? $archivo_nuevo : $actual["archivo"],
        ":estado"       => $estado,
        ":visibilidad"  => $visibilidad,
        ":id_documento" => $id_documento,
    ]);
} catch (PDOException $ex) {
    if ($archivo_nuevo !== "") {
        @unlink(CARPETA_SUBIDAS . $archivo_nuevo);
    }
    throw $ex;
}

// si se reemplazó el archivo, borra el anterior del disco
if ($archivo_nuevo !== "" && $actual["archivo"] !== "") {
    @unlink(CARPETA_SUBIDAS . basename($actual["archivo"]));
}

$_SESSION["flash"] = "Documento actualizado correctamente.";
header("Location: documentos.php");
exit;
