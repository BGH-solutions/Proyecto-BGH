<?php
// Panel de administración: solo para el rol administrador.
require_once __DIR__ . "/../proteger.php";
exigir_admin();

encabezado("Administración", "");
?>
<main class="contenido">
    <h2 class="titulo">Panel de administración</h2>

    <div class="grilla">
        <div class="panel col">
            <h5>Documentos</h5>
            <a href="<?= BASE_URL ?>/admin/documentos/documentos.php" class="btn boton">Gestionar documentos</a>
            <a href="<?= BASE_URL ?>/admin/documentos/asignaciones.php" class="btn boton mt-2">Asignar a pacientes</a>
            <a href="<?= BASE_URL ?>/admin/categorias.php" class="btn boton mt-2">Categorías</a>
        </div>
        <div class="panel col">
            <h5>Encuestas</h5>
            <a href="<?= BASE_URL ?>/admin/encuestas/encuestas.php" class="btn boton">Gestionar encuestas</a>
        </div>
        <div class="panel col">
            <h5>Usuarios</h5>
            <a href="<?= BASE_URL ?>/admin/usuarios.php" class="btn boton">Gestionar usuarios</a>
        </div>
        <div class="panel col">
            <h5 class="text-center">QR del portal para pacientes</h5>
            <?= bloque_qr(url_publica("publico/index.php"), "qr_portal_pacientes") ?>
        </div>
    </div>
</main>
<?php pie(); ?>
