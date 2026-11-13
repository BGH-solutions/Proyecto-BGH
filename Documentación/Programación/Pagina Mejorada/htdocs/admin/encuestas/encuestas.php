<?php
// Lista de encuestas: ver vigencia y respuestas, activar/desactivar y llegar a editar, resultados o QR.
require_once __DIR__ . "/../../proteger.php";
require_once __DIR__ . "/../../conexion.php";
exigir_admin();

// activar / desactivar (por POST con token)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_validar();
    $id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
    if ($id !== false && $id !== null) {
        $conexion->prepare("UPDATE encuestas SET estado = IF(estado = 1, 0, 1) WHERE id_encuesta = :id")
                 ->execute([":id" => $id]);
        $_SESSION["flash"] = "Estado de la encuesta actualizado.";
    }
    header("Location: encuestas.php");
    exit;
}

$encuestas = $conexion->query("
    SELECT e.id_encuesta, e.titulo, e.estado, e.fecha_inicio, e.fecha_fin,
           (SELECT COUNT(*) FROM respuestas_encuestas r WHERE r.id_encuesta = e.id_encuesta) AS respuestas
    FROM encuestas AS e
    ORDER BY e.id_encuesta DESC
")->fetchAll(PDO::FETCH_ASSOC);

$flash = $_SESSION["flash"] ?? "";
unset($_SESSION["flash"]);

encabezado("Encuestas", "encuesta");
?>
<main class="contenido">
    <h2 class="titulo">Encuestas</h2>

    <?php if ($flash !== ""): ?>
        <div class="alert alert-success"><?= e($flash) ?></div>
    <?php endif; ?>

    <div class="tabla-container" style="min-height: 0;">
        <table class="tabla-documentos">
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Vigencia</th>
                    <th>Estado</th>
                    <th>Respuestas</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$encuestas): ?>
                    <tr><td colspan="5">Todavía no hay encuestas. Creá la primera con el botón de abajo.</td></tr>
                <?php endif; ?>
                <?php foreach ($encuestas as $en): ?>
                    <tr class="<?= (int) $en["estado"] === 1 ? "" : "text-muted" ?>">
                        <td><?= e($en["titulo"]) ?></td>
                        <td>
                            <?= e(date("d/m/Y", strtotime($en["fecha_inicio"]))) ?>
                            –
                            <?= $en["fecha_fin"] ? e(date("d/m/Y", strtotime($en["fecha_fin"]))) : "sin fecha de fin" ?>
                        </td>
                        <td><?= (int) $en["estado"] === 1 ? "Activa" : "Inactiva" ?></td>
                        <td><?= (int) $en["respuestas"] ?></td>
                        <td class="d-flex flex-wrap gap-1">
                            <a class="btn btn-sm btn-outline-dark" href="resultados.php?id=<?= (int) $en["id_encuesta"] ?>">Resultados</a>
                            <a class="btn btn-sm btn-outline-secondary" href="resultados.php?id=<?= (int) $en["id_encuesta"] ?>#qr">QR</a>
                            <a class="btn btn-sm btn-outline-primary" href="encuesta_form.php?id=<?= (int) $en["id_encuesta"] ?>">Editar</a>
                            <form method="POST" class="m-0">
                                <?= csrf_campo() ?>
                                <input type="hidden" name="id" value="<?= (int) $en["id_encuesta"] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <?= (int) $en["estado"] === 1 ? "Desactivar" : "Activar" ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="botones mb-3">
        <a href="encuesta_form.php" class="btn boton">Nueva encuesta</a>
    </div>
</main>
<?php pie(); ?>
