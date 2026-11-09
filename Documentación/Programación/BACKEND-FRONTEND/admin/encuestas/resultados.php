<?php
// Resultados de una encuesta: indicadores de satisfacción, filtro por servicio,
// gráficos, comentarios y el QR para que los pacientes la respondan.
require_once __DIR__ . "/../../protegerr.php";
require_once __DIR__ . "/../../conexionn.php";
exigir_admin();

$encuestas = $conexion
    ->query("SELECT id_encuesta, titulo FROM encuestas ORDER BY id_encuesta")
    ->fetchAll(PDO::FETCH_ASSOC);

// encuesta elegida: la de la URL, o la primera
$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
$ids = array_map("intval", array_column($encuestas, "id_encuesta"));
if ($id === false || $id === null || !in_array($id, $ids, true)) {
    $id = $ids[0] ?? 0;
}

// filtro opcional por servicio
$servicios = $conexion
    ->query("SELECT id_servicio, nombre_servicio FROM servicios ORDER BY nombre_servicio")
    ->fetchAll(PDO::FETCH_ASSOC);
$ids_servicio = array_map("intval", array_column($servicios, "id_servicio"));
$id_servicio = filter_input(INPUT_GET, "servicio", FILTER_VALIDATE_INT);
if ($id_servicio === false || $id_servicio === null || !in_array($id_servicio, $ids_servicio, true)) {
    $id_servicio = 0;
}

$total = 0;
$promedios = [0, 0, 0, 0];
$promedio_general = 0.0;
$pct_satisfechos = 0.0;
$distribucion = [0, 0, 0, 0, 0];
$comentarios = [];
$por_servicio = [];

if ($id > 0) {
    $where = "id_encuesta = :id" . ($id_servicio ? " AND id_servicio = :srv" : "");
    $params = [":id" => $id] + ($id_servicio ? [":srv" => $id_servicio] : []);

    $q = $conexion->prepare("
        SELECT COUNT(*) AS total,
               AVG(atencion_personal) AS atencion, AVG(tiempo_de_espera) AS espera,
               AVG(limpieza) AS limpieza, AVG(satisfaccion_general) AS general,
               SUM(satisfaccion_general >= 4) AS satisfechos
        FROM respuestas_encuestas WHERE $where
    ");
    $q->execute($params);
    $r = $q->fetch(PDO::FETCH_ASSOC);

    $total = (int) $r["total"];
    $promedios = [
        round((float) $r["atencion"], 1), round((float) $r["espera"], 1),
        round((float) $r["limpieza"], 1), round((float) $r["general"], 1),
    ];
    $promedio_general = round((float) $r["general"], 1);
    // indicador: % de personas que puntuaron 4 o 5 la satisfacción general
    $pct_satisfechos = $total > 0 ? round((int) $r["satisfechos"] * 100 / $total, 1) : 0.0;

    $q = $conexion->prepare("
        SELECT satisfaccion_general AS valor, COUNT(*) AS n
        FROM respuestas_encuestas WHERE $where GROUP BY satisfaccion_general
    ");
    $q->execute($params);
    foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $f) {
        $v = (int) $f["valor"];
        if ($v >= 1 && $v <= 5) {
            $distribucion[$v - 1] = (int) $f["n"];
        }
    }

    $q = $conexion->prepare("
        SELECT comentario FROM respuestas_encuestas
        WHERE $where AND comentario IS NOT NULL AND comentario <> ''
    ");
    $q->execute($params);
    $comentarios = $q->fetchAll(PDO::FETCH_COLUMN);

    // comparación entre servicios (solo cuando no hay un servicio filtrado)
    if (!$id_servicio) {
        $q = $conexion->prepare("
            SELECT s.nombre_servicio, COUNT(*) AS n,
                   AVG(r.satisfaccion_general) AS prom,
                   SUM(r.satisfaccion_general >= 4) AS sat
            FROM respuestas_encuestas AS r
            INNER JOIN servicios AS s ON s.id_servicio = r.id_servicio
            WHERE r.id_encuesta = :id
            GROUP BY s.id_servicio, s.nombre_servicio
            ORDER BY n DESC
        ");
        $q->execute([":id" => $id]);
        $por_servicio = $q->fetchAll(PDO::FETCH_ASSOC);
    }
}

$datos = json_encode(
    ["promedios" => $promedios, "distribucion" => $distribucion],
    JSON_HEX_TAG | JSON_HEX_AMP
);

encabezado("Resultados de encuestas", "encuesta");
?>
<main>
    <section class="contenido" style="padding-top:20px">

        <div class="grilla-4" style="margin-bottom:20px">
            <?php foreach ($encuestas as $enc): ?>
                <a href="?id=<?= (int) $enc["id_encuesta"] ?>"
                   class="btn btn-enc <?= (int) $enc["id_encuesta"] === $id ? "active" : "" ?>">
                    <?= e($enc["titulo"]) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if ($id === 0): ?>
            <p>Todavía no hay encuestas creadas. <a href="encuesta_form.php">Crear una</a>.</p>
        <?php else: ?>

        <form method="GET" class="d-flex align-items-center justify-content-end gap-2 mb-3">
            <input type="hidden" name="id" value="<?= (int) $id ?>">
            <label for="servicio" class="fw-bold mb-0">Servicio</label>
            <select id="servicio" name="servicio" class="form-select w-auto" onchange="this.form.submit()">
                <option value="0">Todos los servicios</option>
                <?php foreach ($servicios as $s): ?>
                    <option value="<?= (int) $s["id_servicio"] ?>" <?= (int) $s["id_servicio"] === $id_servicio ? "selected" : "" ?>>
                        <?= e($s["nombre_servicio"]) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <div class="grilla">
            <div class="panel justify-content-center text-center">
                <div class="w-100">
                    <p class="fs-5 mb-2">
                        <?= $total > 0
                            ? "Respondida por " . $total . " " . ($total === 1 ? "persona" : "personas") . "."
                            : "Todavía no hay respuestas." ?>
                    </p>
                    <?php if ($total > 0): ?>
                        <p class="mb-0">
                            <strong><?= number_format($pct_satisfechos, 1, ",", "") ?>%</strong> satisfechos (4 o 5)
                            · promedio general <strong><?= number_format($promedio_general, 1, ",", "") ?></strong> de 5
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="panel">
                <p class="mb-0" style="width:35%">Satisfacción general (1 a 5)</p>
                <div class="chart-box"><canvas id="graficoTorta"></canvas></div>
            </div>

            <div class="panel">
                <p class="mb-0" style="width:25%">Promedio por aspecto (1 a 5)</p>
                <div class="chart-box"><canvas id="graficoBarras"></canvas></div>
            </div>

            <div class="panel col">
                <h5 class="text-center">Comentarios:</h5>
                <ul class="mb-0">
                    <?php if (!$comentarios): ?>
                        <li class="list-unstyled text-muted">Sin comentarios.</li>
                    <?php endif; ?>
                    <?php foreach ($comentarios as $c): ?>
                        <li>“<?= e($c) ?>”</li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <?php if ($por_servicio): ?>
            <div class="panel col mt-4" style="margin-top:20px">
                <h5 class="text-center">Comparación por servicio</h5>
                <table class="table table-sm mb-0">
                    <thead><tr><th>Servicio</th><th>Respuestas</th><th>Promedio general</th><th>% satisfechos</th></tr></thead>
                    <tbody>
                        <?php foreach ($por_servicio as $f): ?>
                            <tr>
                                <td><?= e($f["nombre_servicio"]) ?></td>
                                <td><?= (int) $f["n"] ?></td>
                                <td><?= number_format((float) $f["prom"], 1, ",", "") ?></td>
                                <td><?= number_format((int) $f["sat"] * 100 / max(1, (int) $f["n"]), 1, ",", "") ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="panel col mt-4" id="qr-seccion" style="margin-top:20px">
            <h5 class="text-center">QR para responder esta encuesta</h5>
            <?= bloque_qr(url_publica("encuestas/encuestaa.php?id_encuesta=" . $id), "qr_encuesta_" . $id) ?>
        </div>

        <?php endif; ?>
    </section>
</main>
<?php
$scripts = <<<HTML
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const datos = $datos;
const AZUL_CLARO = "#74cbea", AZUL = "#0088cc";

const torta = document.getElementById("graficoTorta");
if (torta) {
  new Chart(torta, {
    type: "pie",
    data: {
      labels: ["1", "2", "3", "4", "5"],
      datasets: [{ data: datos.distribucion, borderWidth: 0,
        backgroundColor: ["#b8e3f5", AZUL_CLARO, "#4fb3e0", "#1e9bd6", AZUL] }]
    },
    options: { maintainAspectRatio: false, plugins: { legend: { position: "left", labels: { boxWidth: 12 } } } }
  });

  new Chart(document.getElementById("graficoBarras"), {
    type: "bar",
    data: {
      labels: ["Atención del personal", "Tiempo de espera", "Limpieza", "Satisfacción general"],
      datasets: [{ data: datos.promedios, backgroundColor: AZUL_CLARO }]
    },
    options: {
      indexAxis: "y", maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: { x: { min: 0, max: 5 } }
    }
  });
}
</script>
HTML;
pie($scripts);
