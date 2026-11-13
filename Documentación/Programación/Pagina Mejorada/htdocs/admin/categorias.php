<?php
// Gestión de categorías de documentos: crear, renombrar y eliminar (solo administrador).
require_once __DIR__ . "/../proteger.php";
require_once __DIR__ . "/../conexion.php";
exigir_admin();

$mensaje = "";
$tipo = "success";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_validar();
    $accion = $_POST["accion"] ?? "";
    $nombre = trim($_POST["nombre"] ?? "");
    $id = filter_input(INPUT_POST, "id_categoria", FILTER_VALIDATE_INT);

    if ($accion === "crear" || $accion === "renombrar") {
        if ($nombre === "" || mb_strlen($nombre) > 150) {
            $mensaje = "El nombre es obligatorio y no puede superar los 150 caracteres.";
            $tipo = "danger";
        } else {
            // evita duplicados (otra categoría con el mismo nombre)
            $q = $conexion->prepare("SELECT 1 FROM categorias_documentos WHERE nombre_categoria = :n AND id_categoria <> :id");
            $q->execute([":n" => $nombre, ":id" => $accion === "renombrar" ? (int) $id : 0]);
            if ($q->fetch()) {
                $mensaje = "Ya existe una categoría con ese nombre.";
                $tipo = "danger";
            } elseif ($accion === "crear") {
                $conexion->prepare("INSERT INTO categorias_documentos (nombre_categoria) VALUES (:n)")->execute([":n" => $nombre]);
                $mensaje = "Categoría creada.";
            } elseif ($id !== false && $id !== null) {
                $conexion->prepare("UPDATE categorias_documentos SET nombre_categoria = :n WHERE id_categoria = :id")
                         ->execute([":n" => $nombre, ":id" => $id]);
                $mensaje = "Categoría actualizada.";
            }
        }
    } elseif ($accion === "eliminar" && $id !== false && $id !== null) {
        // no se puede borrar una categoría que tiene documentos
        $q = $conexion->prepare("SELECT COUNT(*) FROM documentos WHERE id_categoria = :id");
        $q->execute([":id" => $id]);
        if ((int) $q->fetchColumn() > 0) {
            $mensaje = "No se puede eliminar: tiene documentos asociados.";
            $tipo = "danger";
        } else {
            $conexion->prepare("DELETE FROM categorias_documentos WHERE id_categoria = :id")->execute([":id" => $id]);
            $mensaje = "Categoría eliminada.";
        }
    }
}

$categorias = $conexion->query("
    SELECT c.id_categoria, c.nombre_categoria,
           (SELECT COUNT(*) FROM documentos d WHERE d.id_categoria = c.id_categoria) AS documentos
    FROM categorias_documentos AS c
    ORDER BY c.nombre_categoria
")->fetchAll(PDO::FETCH_ASSOC);

encabezado("Categorías", "documentos");
?>
<main class="contenido">
    <h2 class="titulo">Categorías de documentos</h2>

    <?php if ($mensaje !== ""): ?>
        <div class="alert alert-<?= $tipo ?>"><?= e($mensaje) ?></div>
    <?php endif; ?>

    <form method="POST" class="d-flex gap-2 mb-4">
        <?= csrf_campo() ?>
        <input type="hidden" name="accion" value="crear">
        <input type="text" name="nombre" class="form-control" maxlength="150" placeholder="Nombre de la nueva categoría" required>
        <button type="submit" class="btn btn-primary text-nowrap">Agregar</button>
    </form>

    <div class="tabla-container" style="min-height: 0;">
        <table class="tabla-documentos">
            <thead><tr><th>Nombre</th><th>Documentos</th><th>Acciones</th></tr></thead>
            <tbody>
                <?php foreach ($categorias as $c): ?>
                    <tr>
                        <td>
                            <form method="POST" class="d-flex gap-2 m-0">
                                <?= csrf_campo() ?>
                                <input type="hidden" name="accion" value="renombrar">
                                <input type="hidden" name="id_categoria" value="<?= (int) $c["id_categoria"] ?>">
                                <input type="text" name="nombre" class="form-control form-control-sm" maxlength="150"
                                       value="<?= e($c["nombre_categoria"]) ?>" required>
                                <button type="submit" class="btn btn-sm btn-outline-primary">Guardar</button>
                            </form>
                        </td>
                        <td><?= (int) $c["documentos"] ?></td>
                        <td>
                            <form method="POST" class="m-0" onsubmit="return confirm('¿Eliminar esta categoría?');">
                                <?= csrf_campo() ?>
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="id_categoria" value="<?= (int) $c["id_categoria"] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger" <?= (int) $c["documentos"] > 0 ? "disabled" : "" ?>>Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
<?php pie(); ?>
