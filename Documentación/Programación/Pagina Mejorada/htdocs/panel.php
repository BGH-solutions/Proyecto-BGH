<?php
// Panel general: para cualquier persona que haya iniciado sesión.
// proteger.php la manda al login si no hay sesión.
require_once __DIR__ . "/proteger.php";

encabezado("Inicio", "");
?>
<main>
    <h2 class="titulo">Bienvenido/a <?= e($_SESSION["nombre"]) ?> al Hospital de Clínicas</h2>
    <?php if (es_paciente()): ?>
        <div class="text-center my-3">
            <a href="<?= e(BASE_URL) ?>/paciente/documentos.php" class="btn boton">Ver mis documentos</a>
        </div>
    <?php endif; ?>
    <img src="<?= BASE_URL ?>/logo.png" class="imagen" alt="Hospital de Clínicas">
</main>
<?php pie(); ?>
