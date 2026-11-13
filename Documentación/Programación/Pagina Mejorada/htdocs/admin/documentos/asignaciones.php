<?php
require_once __DIR__ . "/../../proteger.php";
exigir_admin();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_validar();
    $id_paciente = filter_input(INPUT_POST, "id_paciente", FILTER_VALIDATE_INT);
    $id_documento = filter_input(INPUT_POST, "id_documento", FILTER_VALIDATE_INT);
    $accion = $_POST["accion"] ?? "";

    if ($id_paciente <= 0 || $id_documento <= 0 || !in_array($accion, ["asignar", "quitar"], true)) {
        http_response_code(400);
        exit("Seleccioná un paciente y un documento válidos.");
    }

    if ($accion === "asignar") {
        $consulta = $conexion->prepare("
            INSERT INTO documentos_pacientes (id_paciente, id_documento)
            SELECT u.id_usuario, d.id_documento
            FROM usuarios AS u
            JOIN documentos AS d ON d.id_documento = :documento
            WHERE u.id_usuario = :paciente AND u.id_rol = :rol AND u.estado = 1
              AND d.estado = 1 AND d.visibilidad IN ('publico', 'privado')
            ON DUPLICATE KEY UPDATE fecha_asignacion = documentos_pacientes.fecha_asignacion
        ");
        $consulta->execute([":paciente" => $id_paciente, ":documento" => $id_documento, ":rol" => ROL_PACIENTE]);
        $_SESSION["aviso_asignaciones"] = $consulta->rowCount()
            ? ["success", "Documento asignado."]
            : ["warning", "El documento ya estaba asignado o la selección dejó de estar disponible."];
    } else {
        $consulta = $conexion->prepare("
            DELETE FROM documentos_pacientes WHERE id_paciente = :paciente AND id_documento = :documento
        ");
        $consulta->execute([":paciente" => $id_paciente, ":documento" => $id_documento]);
        $_SESSION["aviso_asignaciones"] = ["success", "Asignación quitada."];
    }

    header("Location: asignaciones.php", true, 303);
    exit;
}

$consulta = $conexion->prepare("
    SELECT id_usuario, nombre, apellido, email
    FROM usuarios WHERE id_rol = :rol AND estado = 1
    ORDER BY apellido, nombre, email
");
$consulta->execute([":rol" => ROL_PACIENTE]);
$pacientes = $consulta->fetchAll(PDO::FETCH_ASSOC);

$documentos = $conexion->query("
    SELECT id_documento, titulo, visibilidad
    FROM documentos WHERE estado = 1 AND visibilidad IN ('publico', 'privado')
    ORDER BY titulo, id_documento
")->fetchAll(PDO::FETCH_ASSOC);

$asignaciones = $conexion->query("
    SELECT a.id_paciente, a.id_documento, u.nombre, u.apellido, u.email, d.titulo
    FROM documentos_pacientes AS a
    JOIN usuarios AS u ON u.id_usuario = a.id_paciente
    JOIN documentos AS d ON d.id_documento = a.id_documento
    ORDER BY a.fecha_asignacion DESC, u.apellido, u.nombre, d.titulo
")->fetchAll(PDO::FETCH_ASSOC);

$aviso = $_SESSION["aviso_asignaciones"] ?? null;
unset($_SESSION["aviso_asignaciones"]);
encabezado("Asignar documentos", "documentos");
?>
<main class="contenido">
    <h2 class="titulo">Asignar documentos a pacientes</h2>
    <?php if ($aviso): ?>
        <div class="alert alert-<?= e($aviso[0]) ?>"><?= e($aviso[1]) ?></div>
    <?php endif; ?>

    <div class="panel mb-4">
        <?php if ($pacientes && $documentos): ?>
            <form method="POST">
                <?= csrf_campo() ?>
                <input type="hidden" name="accion" value="asignar">
                <label class="form-label" for="id_paciente">Paciente</label>
                <select class="form-select mb-3" id="id_paciente" name="id_paciente" required>
                    <option value="">Seleccioná un paciente</option>
                    <?php foreach ($pacientes as $p): ?>
                        <option value="<?= (int) $p["id_usuario"] ?>"><?= e($p["apellido"] . ", " . $p["nombre"] . " (" . $p["email"] . ")") ?></option>
                    <?php endforeach; ?>
                </select>
                <label class="form-label" for="id_documento">Documento</label>
                <select class="form-select mb-3" id="id_documento" name="id_documento" required>
                    <option value="">Seleccioná un documento</option>
                    <?php foreach ($documentos as $d): ?>
                        <option value="<?= (int) $d["id_documento"] ?>"><?= e($d["titulo"] . " (" . VISIBILIDADES_DOCUMENTOS[$d["visibilidad"]] . ")") ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn boton" type="submit">Asignar documento</button>
            </form>
        <?php else: ?>
            <p>Para asignar un documento tiene que haber al menos un paciente activo y un documento público o privado activo.</p>
        <?php endif; ?>
        <a href="documento_nuevo.php?privado=1" class="btn btn-outline-secondary mt-2">Agregar documento privado</a>
    </div>

    <h3 class="h5">Asignaciones actuales</h3>
    <?php if (!$asignaciones): ?>
        <p>Todavía no hay documentos asignados.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="tabla-documentos">
                <thead><tr><th>Paciente</th><th>Documento</th><th>Acción</th></tr></thead>
                <tbody>
                <?php foreach ($asignaciones as $a): ?>
                    <tr>
                        <td><?= e($a["nombre"] . " " . $a["apellido"] . " (" . $a["email"] . ")") ?></td>
                        <td><?= e($a["titulo"]) ?></td>
                        <td>
                            <form method="POST" class="m-0" onsubmit="return confirm('¿Quitar esta asignación?');">
                                <?= csrf_campo() ?>
                                <input type="hidden" name="accion" value="quitar">
                                <input type="hidden" name="id_paciente" value="<?= (int) $a["id_paciente"] ?>">
                                <input type="hidden" name="id_documento" value="<?= (int) $a["id_documento"] ?>">
                                <button class="btn btn-sm btn-outline-danger" type="submit">Quitar asignación</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>
<?php pie(); ?>
