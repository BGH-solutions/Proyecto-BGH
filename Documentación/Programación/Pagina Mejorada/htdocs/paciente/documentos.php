<?php
require_once __DIR__ . "/../proteger.php";
exigir_paciente();
header("Cache-Control: private, no-store");

$consulta = $conexion->prepare("
    SELECT d.id_documento, d.titulo, d.descripcion, a.fecha_asignacion
    FROM documentos_pacientes AS a
    JOIN documentos AS d ON d.id_documento = a.id_documento
    WHERE a.id_paciente = :paciente AND d.estado = 1 AND d.visibilidad IN ('publico', 'privado')
    ORDER BY a.fecha_asignacion DESC, d.id_documento DESC
");
$consulta->execute([":paciente" => $_SESSION["id_usuario"]]);
$documentos = $consulta->fetchAll(PDO::FETCH_ASSOC);

encabezado("Mis documentos", "mis_documentos");
?>
<main class="contenido">
    <h2 class="titulo">Mis documentos</h2>
    <?php if (!$documentos): ?>
        <p class="alert alert-info">Todavía no tenés documentos asignados.</p>
    <?php endif; ?>
    <?php foreach ($documentos as $d): ?>
        <article class="panel mb-3 text-break">
            <h3 class="h5"><?= e($d["titulo"]) ?></h3>
            <?php if ($d["descripcion"] !== null && $d["descripcion"] !== ""): ?>
                <p><?= nl2br(e($d["descripcion"])) ?></p>
            <?php endif; ?>
            <p class="small text-muted">Asignado el <?= e(date("d/m/Y", strtotime($d["fecha_asignacion"]))) ?></p>
            <a class="btn boton" href="archivo.php?id=<?= (int) $d["id_documento"] ?>">Abrir documento</a>
        </article>
    <?php endforeach; ?>
</main>
<?php pie(); ?>
