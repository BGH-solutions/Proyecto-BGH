<?php
// Listado de documentos. Lo ven todos los usuarios con sesión,
// pero cada rol solo ve las visibilidades que le corresponden.
require_once __DIR__ . "/../../proteger.php";
require_once __DIR__ . "/../../conexion.php";

// ---- orden elegido en el filtro ----
$ordenes = [
    "nombre"    => "d.titulo ASC",
    "categoria" => "c.nombre_categoria ASC, d.titulo ASC",
    "fecha"     => "d.fecha_subida DESC, d.id_documento DESC",
];
$orden = isset($_GET["orden"]) && is_string($_GET["orden"]) && isset($ordenes[$_GET["orden"]])
    ? $_GET["orden"]
    : "fecha";

// ---- qué documentos puede ver este usuario ----
$condiciones = [];
$parametros = [];

if (!es_admin()) {
    $condiciones[] = "d.estado = 1";
    $permitidas = VISIBILIDAD_POR_ROL[(int) $_SESSION["id_rol"]] ?? ["publico"];
    $marcas = [];
    foreach ($permitidas as $i => $v) {
        $marcas[] = ":vis" . $i;
        $parametros[":vis" . $i] = $v;
    }
    $condiciones[] = "d.visibilidad IN (" . implode(",", $marcas) . ")";
}

$sql = "
    SELECT d.id_documento, d.titulo, d.fecha_subida, d.estado, d.visibilidad,
           c.nombre_categoria
    FROM documentos AS d
    INNER JOIN categorias_documentos AS c ON d.id_categoria = c.id_categoria
    " . ($condiciones ? "WHERE " . implode(" AND ", $condiciones) : "") . "
    ORDER BY " . $ordenes[$orden];

$consulta = $conexion->prepare($sql);
$consulta->execute($parametros);
$documentos = $consulta->fetchAll(PDO::FETCH_ASSOC);

// mensaje que dejó otra página (guardar, editar, eliminar)
$flash = $_SESSION["flash"] ?? "";
unset($_SESSION["flash"]);

encabezado("Documentos", "documentos");
?>
<main class="contenido">

    <h2 class="titulo">Documentos agregados recientemente</h2>

    <?php if ($flash !== ""): ?>
        <div class="alert alert-success"><?= e($flash) ?></div>
    <?php endif; ?>

    <form method="GET" class="filtro d-flex align-items-center justify-content-end gap-2 mb-3">
        <label for="orden" class="fw-bold mb-0">Filtrar <i class="bi bi-funnel"></i></label>
        <select id="orden" name="orden" class="form-select w-auto" onchange="this.form.submit()">
            <option value="nombre" <?= $orden === "nombre" ? "selected" : "" ?>>Nombre (A-Z)</option>
            <option value="categoria" <?= $orden === "categoria" ? "selected" : "" ?>>Categoría (A-Z)</option>
            <option value="fecha" <?= $orden === "fecha" ? "selected" : "" ?>>Fecha (Más reciente)</option>
        </select>
    </form>

    <div class="tabla-container">
        <table class="tabla-documentos">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Fecha</th>
                    <?php if (es_admin()): ?>
                        <th>Visibilidad</th>
                        <th>Estado</th>
                    <?php endif; ?>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($documentos) === 0): ?>
                    <tr><td colspan="6">No hay documentos disponibles.</td></tr>
                <?php endif; ?>

                <?php foreach ($documentos as $d): ?>
                    <tr class="<?= (int) $d["estado"] === 1 ? "" : "text-muted" ?>">
                        <td><?= e($d["titulo"]) ?></td>
                        <td><?= e($d["nombre_categoria"]) ?></td>
                        <td><?= e(date("d/m/Y", strtotime($d["fecha_subida"]))) ?></td>
                        <?php if (es_admin()): ?>
                            <td><?= e(VISIBILIDADES_DOCUMENTOS[$d["visibilidad"]] ?? $d["visibilidad"]) ?></td>
                            <td><?= (int) $d["estado"] === 1 ? "Activo" : "Inactivo" ?></td>
                        <?php endif; ?>
                        <td class="d-flex flex-wrap gap-1">
                            <a class="btn btn-sm btn-outline-dark" href="documento_ver.php?id=<?= (int) $d["id_documento"] ?>">Ver</a>
                            <a class="btn btn-sm btn-outline-dark" href="descargar.php?id=<?= (int) $d["id_documento"] ?>">Descargar</a>
                            <?php if (es_admin()): ?>
                                <a class="btn btn-sm btn-outline-secondary" href="documento_qr.php?id=<?= (int) $d["id_documento"] ?>">QR</a>
                                <a class="btn btn-sm btn-outline-primary" href="documento_editar.php?id=<?= (int) $d["id_documento"] ?>">Editar</a>
                                <form action="documento_eliminar.php" method="POST" class="m-0"
                                      onsubmit="return confirm('¿Eliminar este documento?');">
                                    <?= csrf_campo() ?>
                                    <input type="hidden" name="id" value="<?= (int) $d["id_documento"] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if (es_admin()): ?>
        <div class="botones mb-3">
            <a href="asignaciones.php" class="btn boton">Asignar a pacientes</a>
            <a href="documento_nuevo.php" class="btn boton">Agregar documento</a>
        </div>
    <?php endif; ?>
</main>
<?php pie(); ?>
