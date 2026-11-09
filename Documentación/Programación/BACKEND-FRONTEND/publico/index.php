<?php
// Portal para pacientes: SIN login, pensado para celular.
// Solo muestra documentos activos con visibilidad "publico" y encuestas vigentes.
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../includes/funciones.php";
require_once __DIR__ . "/../conexionn.php";

$filas = $conexion->query("
    SELECT d.id_documento, d.titulo, c.nombre_categoria
    FROM documentos AS d
    INNER JOIN categorias_documentos AS c ON d.id_categoria = c.id_categoria
    WHERE d.estado = 1 AND d.visibilidad = 'publico'
    ORDER BY c.nombre_categoria, d.titulo
")->fetchAll(PDO::FETCH_ASSOC);

// agrupa los documentos por categoría
$por_categoria = [];
foreach ($filas as $f) {
    $por_categoria[$f["nombre_categoria"]][] = $f;
}

$encuestas = $conexion->query("
    SELECT id_encuesta, titulo FROM encuestas
    WHERE estado = 1
      AND fecha_inicio <= CURRENT_DATE()
      AND (fecha_fin IS NULL OR fecha_fin >= CURRENT_DATE())
    ORDER BY id_encuesta
")->fetchAll(PDO::FETCH_ASSOC);

encabezado_movil("Documentos para pacientes");
?>
<main class="container py-3" style="max-width: 720px;">
    <h1 class="h4 mb-3">Documentos para pacientes</h1>

    <input type="search" id="buscar" class="form-control mb-3" placeholder="Buscar documento…" aria-label="Buscar documento">

    <?php if (!$por_categoria): ?>
        <div class="alert alert-info">Todavía no hay documentos disponibles.</div>
    <?php endif; ?>

    <?php foreach ($por_categoria as $categoria => $docs): ?>
        <section class="categoria mb-4">
            <h2 class="h6 text-uppercase text-muted"><?= e($categoria) ?></h2>
            <div class="list-group">
                <?php foreach ($docs as $d): ?>
                    <a class="list-group-item list-group-item-action doc"
                       href="documento.php?id=<?= (int) $d["id_documento"] ?>">
                        <?= e($d["titulo"]) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>

    <?php if ($encuestas): ?>
        <h2 class="h6 text-uppercase text-muted mt-4">Encuestas de satisfacción</h2>
        <div class="list-group mb-4">
            <?php foreach ($encuestas as $e): ?>
                <a class="list-group-item list-group-item-action"
                   href="<?= BASE_URL ?>/encuestas/encuestaa.php?id_encuesta=<?= (int) $e["id_encuesta"] ?>">
                    <?= e($e["titulo"]) ?> <span class="text-muted small">(anónima)</span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
<?php
$js = <<<'HTML'
<script>
// filtra la lista mientras se escribe
document.getElementById("buscar").addEventListener("input", function () {
    var q = this.value.toLowerCase();
    document.querySelectorAll(".doc").forEach(function (a) {
        a.style.display = a.textContent.toLowerCase().includes(q) ? "" : "none";
    });
    document.querySelectorAll(".categoria").forEach(function (s) {
        var hay = Array.from(s.querySelectorAll(".doc")).some(function (a) { return a.style.display !== "none"; });
        s.style.display = hay ? "" : "none";
    });
});
</script>
HTML;
pie_movil($js);
