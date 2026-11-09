<?php
// Panel general: para cualquier persona que haya iniciado sesión.
// protegerr.php la manda al login si no hay sesión.
require_once "protegerr.php";

encabezado("Inicio", "");
?>
<main>
    <h2 class="titulo">Bienvenido/a <?= e($_SESSION["nombre"]) ?> al Hospital de Clínicas</h2>
    <img src="<?= BASE_URL ?>/logo.png" class="imagen" alt="Hospital de Clínicas">
</main>
<?php pie(); ?>
