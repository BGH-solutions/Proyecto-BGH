<?php
// Formulario para editar un documento existente (solo administrador)
require_once __DIR__ . "/../../protegerr.php";
require_once __DIR__ . "/../../conexionn.php";
exigir_admin();

$id_documento = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if ($id_documento === false || $id_documento === null) {
    http_response_code(400);
    exit("El documento seleccionado no es válido");
}

$consulta = $conexion->prepare("SELECT * FROM documentos WHERE id_documento = :id");
$consulta->execute([":id" => $id_documento]);
$d = $consulta->fetch(PDO::FETCH_ASSOC);

if (!$d) {
    http_response_code(404);
    exit("El documento no existe");
}

$categorias = $conexion
    ->query("SELECT id_categoria, nombre_categoria FROM categorias_documentos ORDER BY nombre_categoria")
    ->fetchAll(PDO::FETCH_ASSOC);

$visibilidades = ["publico" => "Público", "medico" => "Médico", "enfermero" => "Enfermero"];

encabezado("Editar documento", "documentos");
?>
<main class="contenido">
    <div class="card mx-auto" style="max-width: 560px;">
        <div class="card-body">
            <h3 class="mb-4">Editar documento</h3>

            <form action="documento_actualizarr.php" method="POST" enctype="multipart/form-data">
                <?= csrf_campo() ?>
                <input type="hidden" name="id_documento" value="<?= (int) $d["id_documento"] ?>">

                <div class="mb-3">
                    <label for="titulo" class="form-label">Nombre personalizado:</label>
                    <input type="text" id="titulo" name="titulo" class="form-control" maxlength="100"
                           value="<?= e($d["titulo"]) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="id_categoria" class="form-label">Categoría:</label>
                    <select id="id_categoria" name="id_categoria" class="form-select" required>
                        <?php foreach ($categorias as $c): ?>
                            <option value="<?= (int) $c["id_categoria"] ?>"
                                <?= (int) $c["id_categoria"] === (int) $d["id_categoria"] ? "selected" : "" ?>>
                                <?= e($c["nombre_categoria"]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción:</label>
                    <textarea id="descripcion" name="descripcion" class="form-control" rows="4"><?= e($d["descripcion"] ?? "") ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="archivo" class="form-label">Reemplazar archivo (opcional):</label>
                    <input type="file" id="archivo" name="archivo" class="form-control"
                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                    <div class="form-text">
                        Archivo actual:
                        <a href="descargar.php?id=<?= (int) $d["id_documento"] ?>">descargar</a>.
                        Si no elegís otro, se mantiene.
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="visibilidad" class="form-label">Visibilidad:</label>
                        <select id="visibilidad" name="visibilidad" class="form-select" required>
                            <?php foreach ($visibilidades as $valor => $texto): ?>
                                <option value="<?= $valor ?>" <?= $d["visibilidad"] === $valor ? "selected" : "" ?>><?= $texto ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Solo los documentos <strong>Públicos</strong> y activos se abren al escanear el QR.</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="estado" class="form-label">Estado:</label>
                        <select id="estado" name="estado" class="form-select" required>
                            <option value="1" <?= (int) $d["estado"] === 1 ? "selected" : "" ?>>Activo</option>
                            <option value="0" <?= (int) $d["estado"] === 0 ? "selected" : "" ?>>Inactivo</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="documentoss.php" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</main>
<?php pie(); ?>
