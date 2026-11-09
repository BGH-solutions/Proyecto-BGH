<?php
// Formulario para crear un documento (solo administrador)
require_once __DIR__ . "/../../protegerr.php";
require_once __DIR__ . "/../../conexionn.php";
exigir_admin();

$categorias = $conexion
    ->query("SELECT id_categoria, nombre_categoria FROM categorias_documentos ORDER BY nombre_categoria")
    ->fetchAll(PDO::FETCH_ASSOC);

encabezado("Agregar documento", "documentos");
?>
<main class="contenido">
    <div class="card mx-auto" style="max-width: 560px;">
        <div class="card-body">
            <h3 class="mb-4">Subir documento</h3>

            <!-- enctype multipart/form-data es obligatorio para subir archivos -->
            <form action="documento_guardarr.php" method="POST" enctype="multipart/form-data">
                <?= csrf_campo() ?>

                <div class="mb-3">
                    <label for="titulo" class="form-label">Nombre personalizado:</label>
                    <input type="text" id="titulo" name="titulo" class="form-control" maxlength="100" required>
                </div>

                <div class="mb-3">
                    <label for="id_categoria" class="form-label">Categoría:</label>
                    <select id="id_categoria" name="id_categoria" class="form-select" required>
                        <option value="">Seleccione una categoría</option>
                        <?php foreach ($categorias as $c): ?>
                            <option value="<?= (int) $c["id_categoria"] ?>"><?= e($c["nombre_categoria"]) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción:</label>
                    <textarea id="descripcion" name="descripcion" class="form-control" rows="4"></textarea>
                </div>

                <div class="mb-3">
                    <label for="archivo" class="form-label">Archivo (PDF, Word, JPG o PNG, máx. 10 MB):</label>
                    <input type="file" id="archivo" name="archivo" class="form-control"
                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="visibilidad" class="form-label">Visibilidad:</label>
                        <select id="visibilidad" name="visibilidad" class="form-select" required>
                            <option value="publico">Público</option>
                            <option value="medico">Médico</option>
                            <option value="enfermero">Enfermero</option>
                        </select>
                        <div class="form-text">Solo los documentos <strong>Públicos</strong> y activos se abren al escanear el QR.</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="estado" class="form-label">Estado:</label>
                        <select id="estado" name="estado" class="form-select" required>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="documentoss.php" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</main>
<?php pie(); ?>
