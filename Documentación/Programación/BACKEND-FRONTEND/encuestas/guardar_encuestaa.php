<?php
// Recibe y guarda las respuestas de la encuesta anónima.
session_start();
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../includes/funciones.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: encuestaa.php");
    exit;
}

require_once __DIR__ . "/../conexionn.php";

// lee un campo POST como entero dentro de un rango (false = inválido, null = no llegó)
function recibirEntero(string $campo, int $minimo, int $maximo)
{
    return filter_input(INPUT_POST, $campo, FILTER_VALIDATE_INT, [
        "options" => ["min_range" => $minimo, "max_range" => $maximo],
    ]);
}

$id_encuesta = recibirEntero("id_encuesta", 1, PHP_INT_MAX);
$id_servicio = recibirEntero("id_servicio", 1, PHP_INT_MAX);

$calificaciones = [
    "atencion_personal"    => recibirEntero("atencion_personal", 1, 5),
    "tiempo_de_espera"     => recibirEntero("tiempo_de_espera", 1, 5),
    "limpieza"             => recibirEntero("limpieza", 1, 5),
    "satisfaccion_general" => recibirEntero("satisfaccion_general", 1, 5),
];

$errores = [];
$comentario = "";

if ($id_encuesta === false || $id_encuesta === null) {
    $errores[] = "La encuesta seleccionada no es válida";
}
if ($id_servicio === false || $id_servicio === null) {
    $errores[] = "Seleccioná un servicio válido";
}
foreach ($calificaciones as $valor) {
    if ($valor === false || $valor === null) {
        $errores[] = "Respondé todas las preguntas con un valor entre 1 y 5";
        break;
    }
}

$comentario_recibido = $_POST["comentario"] ?? "";
if (!is_string($comentario_recibido)) {
    $errores[] = "El comentario no es válido";
} else {
    $comentario = trim($comentario_recibido);
    if (mb_strlen($comentario, "UTF-8") > 200) {
        $errores[] = "El comentario no puede superar los 200 caracteres";
    }
}

if (count($errores) === 0) {
    try {
        // la encuesta puede haber cambiado desde que se abrió el formulario
        $q = $conexion->prepare("
            SELECT id_encuesta FROM encuestas
            WHERE id_encuesta = :id AND estado = 1
              AND fecha_inicio <= CURRENT_DATE()
              AND (fecha_fin IS NULL OR fecha_fin >= CURRENT_DATE())
            LIMIT 1
        ");
        $q->execute([":id" => $id_encuesta]);
        if (!$q->fetch(PDO::FETCH_ASSOC)) {
            $errores[] = "La encuesta no existe o ya no está activa";
        }

        $q = $conexion->prepare("
            SELECT id_servicio FROM servicios
            WHERE id_servicio = :id AND estado = 1 LIMIT 1
        ");
        $q->execute([":id" => $id_servicio]);
        if (!$q->fetch(PDO::FETCH_ASSOC)) {
            $errores[] = "El servicio no existe o no está disponible";
        }

        if (count($errores) === 0) {
            $conexion->prepare("
                INSERT INTO respuestas_encuestas
                (id_encuesta, id_servicio, atencion_personal, tiempo_de_espera,
                 limpieza, satisfaccion_general, comentario)
                VALUES
                (:id_encuesta, :id_servicio, :atencion_personal, :tiempo_de_espera,
                 :limpieza, :satisfaccion_general, :comentario)
            ")->execute([
                ":id_encuesta"          => $id_encuesta,
                ":id_servicio"          => $id_servicio,
                ":atencion_personal"    => $calificaciones["atencion_personal"],
                ":tiempo_de_espera"     => $calificaciones["tiempo_de_espera"],
                ":limpieza"             => $calificaciones["limpieza"],
                ":satisfaccion_general" => $calificaciones["satisfaccion_general"],
                ":comentario"           => $comentario !== "" ? $comentario : null,
            ]);

            // redirige a una página GET para que recargar no reenvíe la encuesta
            header("Location: graciass.php", true, 303);
            exit;
        }
    } catch (PDOException $ex) {
        // el detalle técnico va al log, no al usuario
        error_log("Error al guardar encuesta: " . $ex->getMessage());
        http_response_code(500);
        $errores[] = "No se logró guardar la encuesta. Intentá más tarde";
    }
}

if (http_response_code() !== 500) {
    http_response_code(400);
}

$url_volver = "encuestaa.php";
if (is_int($id_encuesta) && $id_encuesta > 0) {
    $url_volver .= "?id_encuesta=" . $id_encuesta;
    if (is_int($id_servicio) && $id_servicio > 0) {
        $url_volver .= "&servicio=" . $id_servicio;
    }
}

encabezado("No se pudo enviar la encuesta", "encuesta");
?>
<main class="contenido">
    <div class="alert alert-danger">
        <h4>No se pudo enviar la encuesta</h4>
        <ul class="mb-0">
            <?php foreach ($errores as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
        </ul>
    </div>
    <a href="<?= e($url_volver) ?>" class="btn btn-secondary">Volver a la encuesta</a>
</main>
<?php pie(); ?>
