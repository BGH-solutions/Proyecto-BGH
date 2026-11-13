<?php
// Crear o editar una encuesta (sin id = crear, con ?id= = editar).
require_once __DIR__ . "/../../proteger.php";
require_once __DIR__ . "/../../conexion.php";
exigir_admin();

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if ($id === false) {
    http_response_code(400);
    exit("Encuesta no válida");
}

// valores iniciales del formulario
$datos = ["titulo" => "", "descripcion" => "", "fecha_inicio" => date("Y-m-d"), "fecha_fin" => "", "estado" => 1];

if ($id !== null) {
    $q = $conexion->prepare("SELECT titulo, descripcion, fecha_inicio, fecha_fin, estado FROM encuestas WHERE id_encuesta = :id");
    $q->execute([":id" => $id]);
    $fila = $q->fetch(PDO::FETCH_ASSOC);
    if (!$fila) {
        http_response_code(404);
        exit("La encuesta no existe");
    }
    $datos = [
        "titulo"       => $fila["titulo"],
        "descripcion"  => $fila["descripcion"] ?? "",
        "fecha_inicio" => $fila["fecha_inicio"],
        "fecha_fin"    => $fila["fecha_fin"] ?? "",
        "estado"       => (int) $fila["estado"],
    ];
}

// comprueba que un texto sea una fecha real AAAA-MM-DD
function fecha_valida(string $f): bool
{
    $d = DateTime::createFromFormat("Y-m-d", $f);
    return $d && $d->format("Y-m-d") === $f;
}

$errores = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_validar();

    $datos["titulo"]       = trim($_POST["titulo"] ?? "");
    $datos["descripcion"]  = trim($_POST["descripcion"] ?? "");
    $datos["fecha_inicio"] = trim($_POST["fecha_inicio"] ?? "");
    $datos["fecha_fin"]    = trim($_POST["fecha_fin"] ?? "");
    $datos["estado"]       = filter_input(INPUT_POST, "estado", FILTER_VALIDATE_INT);

    if ($datos["titulo"] === "") {
        $errores[] = "El título es obligatorio";
    } elseif (mb_strlen($datos["titulo"]) > 150) {
        $errores[] = "El título no puede superar los 150 caracteres";
    }
    if (!fecha_valida($datos["fecha_inicio"])) {
        $errores[] = "La fecha de inicio no es válida";
    }
    if ($datos["fecha_fin"] !== "") {
        if (!fecha_valida($datos["fecha_fin"])) {
            $errores[] = "La fecha de fin no es válida";
        } elseif (fecha_valida($datos["fecha_inicio"]) && $datos["fecha_fin"] < $datos["fecha_inicio"]) {
            $errores[] = "La fecha de fin no puede ser anterior a la de inicio";
        }
    }
    if ($datos["estado"] !== 0 && $datos["estado"] !== 1) {
        $errores[] = "El estado no es válido";
    }

    if (!$errores) {
        $parametros = [
            ":titulo"       => $datos["titulo"],
            ":descripcion"  => $datos["descripcion"] !== "" ? $datos["descripcion"] : null,
            ":fecha_inicio" => $datos["fecha_inicio"],
            ":fecha_fin"    => $datos["fecha_fin"] !== "" ? $datos["fecha_fin"] : null,
            ":estado"       => $datos["estado"],
        ];

        if ($id === null) {
            $conexion->prepare("
                INSERT INTO encuestas (titulo, descripcion, fecha_inicio, fecha_fin, estado)
                VALUES (:titulo, :descripcion, :fecha_inicio, :fecha_fin, :estado)
            ")->execute($parametros);
            $_SESSION["flash"] = "Encuesta creada.";
        } else {
            $parametros[":id"] = $id;
            $conexion->prepare("
                UPDATE encuestas
                SET titulo = :titulo, descripcion = :descripcion,
                    fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin, estado = :estado
                WHERE id_encuesta = :id
            ")->execute($parametros);
            $_SESSION["flash"] = "Encuesta actualizada.";
        }
        header("Location: encuestas.php");
        exit;
    }
}

encabezado($id === null ? "Nueva encuesta" : "Editar encuesta", "encuesta");
?>
<main class="contenido">
    <div class="card mx-auto" style="max-width: 560px;">
        <div class="card-body">
            <h3 class="mb-4"><?= $id === null ? "Nueva encuesta" : "Editar encuesta" ?></h3>

            <?php if ($errores): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errores as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST">
                <?= csrf_campo() ?>

                <div class="mb-3">
                    <label for="titulo" class="form-label">Título:</label>
                    <input type="text" id="titulo" name="titulo" class="form-control" maxlength="150"
                           value="<?= e($datos["titulo"]) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción (se muestra al paciente):</label>
                    <textarea id="descripcion" name="descripcion" class="form-control" rows="3"><?= e($datos["descripcion"]) ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="fecha_inicio" class="form-label">Disponible desde:</label>
                        <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control"
                               value="<?= e($datos["fecha_inicio"]) ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="fecha_fin" class="form-label">Hasta (opcional):</label>
                        <input type="date" id="fecha_fin" name="fecha_fin" class="form-control"
                               value="<?= e($datos["fecha_fin"]) ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="estado" class="form-label">Estado:</label>
                    <select id="estado" name="estado" class="form-select">
                        <option value="1" <?= (int) $datos["estado"] === 1 ? "selected" : "" ?>>Activa</option>
                        <option value="0" <?= (int) $datos["estado"] === 0 ? "selected" : "" ?>>Inactiva</option>
                    </select>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="encuestas.php" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</main>
<?php pie(); ?>
