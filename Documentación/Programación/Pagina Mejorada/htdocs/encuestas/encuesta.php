<?php
// Encuesta anónima: no requiere iniciar sesión.
session_start();
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../includes/funciones.php";
require_once __DIR__ . "/../conexion.php";

$id_encuesta = filter_input(INPUT_GET, "id_encuesta", FILTER_VALIDATE_INT);
if ($id_encuesta === null) {
    $id_encuesta = 1;   // si no se indica, usa la encuesta 1
}
if ($id_encuesta === false || $id_encuesta < 1) {
    http_response_code(400);
    exit("El identificador de la encuesta no es válido");
}

// encuesta activa y dentro de su período de disponibilidad
$consulta = $conexion->prepare("
    SELECT id_encuesta, titulo, descripcion
    FROM encuestas
    WHERE id_encuesta = :id_encuesta
      AND estado = 1
      AND fecha_inicio <= CURRENT_DATE()
      AND (fecha_fin IS NULL OR fecha_fin >= CURRENT_DATE())
    LIMIT 1
");
$consulta->execute([":id_encuesta" => $id_encuesta]);
$encuesta = $consulta->fetch(PDO::FETCH_ASSOC);

if (!$encuesta) {
    http_response_code(404);
    exit("La encuesta no existe o no está activa");
}

$servicios = $conexion->query("
    SELECT id_servicio, nombre_servicio
    FROM servicios
    WHERE estado = 1
    ORDER BY nombre_servicio ASC
")->fetchAll(PDO::FETCH_ASSOC);

// permite dejar un servicio preseleccionado desde la URL (?servicio=2)
$servicio_preseleccionado = filter_input(INPUT_GET, "servicio", FILTER_VALIDATE_INT);

// la clave es el name del campo; debe coincidir con guardar_encuesta.php
$preguntas = [
    "atencion_personal"    => "¿Cómo calificarías la atención del personal?",
    "tiempo_de_espera"     => "¿Cómo calificarías el tiempo de espera?",
    "limpieza"             => "¿Cómo calificarías la limpieza del lugar?",
    "satisfaccion_general" => "¿Cómo calificarías tu satisfacción general?",
];

encabezado($encuesta["titulo"], "encuesta");
?>
<main class="contenido">
    <div class="card mx-auto" style="max-width: 680px;">
        <div class="card-body p-4">
            <h3><?= e($encuesta["titulo"]) ?></h3>

            <?php if (!empty($encuesta["descripcion"])): ?>
                <p><?= e($encuesta["descripcion"]) ?></p>
            <?php endif; ?>

            <div class="alert alert-info">
                Encuesta anónima. No necesitás iniciar sesión y no guardamos nombre,
                cédula ni correo. No pongas información personal en los comentarios.
            </div>

            <?php if (count($servicios) === 0): ?>
                <p>No hay servicios disponibles para la encuesta en este momento.</p>
            <?php else: ?>
                <form action="guardar_encuesta.php" method="POST">
                    <input type="hidden" name="id_encuesta" value="<?= (int) $encuesta["id_encuesta"] ?>">

                    <div class="mb-4">
                        <label for="id_servicio" class="form-label">¿Qué servicio del hospital usaste?</label>
                        <select name="id_servicio" id="id_servicio" class="form-select" required>
                            <option value="">Seleccione un servicio</option>
                            <?php foreach ($servicios as $s): ?>
                                <option value="<?= (int) $s["id_servicio"] ?>"
                                    <?= $servicio_preseleccionado === (int) $s["id_servicio"] ? "selected" : "" ?>>
                                    <?= e($s["nombre_servicio"]) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <p class="fw-bold">Calificá cada aspecto del 1 al 5 (1 peor, 5 mejor)</p>

                    <?php foreach ($preguntas as $campo => $texto): ?>
                        <fieldset class="border rounded p-3 mb-3">
                            <legend class="fs-6 fw-bold w-auto px-2"><?= e($texto) ?></legend>
                            <?php for ($valor = 1; $valor <= 5; $valor++): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio"
                                           name="<?= e($campo) ?>" id="<?= e($campo) . $valor ?>"
                                           value="<?= $valor ?>" required>
                                    <label class="form-check-label" for="<?= e($campo) . $valor ?>">
                                        <?= $valor ?>
                                        <?php if ($valor === 1): ?> - Peor valoración
                                        <?php elseif ($valor === 5): ?> - Mejor valoración
                                        <?php endif; ?>
                                    </label>
                                </div>
                            <?php endfor; ?>
                        </fieldset>
                    <?php endforeach; ?>

                    <div class="mb-3">
                        <label for="comentario" class="form-label">¿Qué podríamos mejorar? (opcional)</label>
                        <textarea name="comentario" id="comentario" class="form-control" rows="4"
                                  maxlength="200"
                                  placeholder="Hasta 200 caracteres, no ponga datos personales"></textarea>
                    </div>

                    <button type="submit" class="btn boton w-100">Enviar</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</main>
<?php pie(); ?>
