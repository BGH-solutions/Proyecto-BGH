<?php
session_start();
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../includes/funciones.php";

encabezado("Encuesta enviada", "encuesta");
?>
<main class="contenido text-center">
    <h2 class="titulo">¡Gracias por responder!</h2>
    <p class="fs-5">Tu encuesta fue enviada correctamente.</p>
    <a href="<?= BASE_URL ?>/<?= isset($_SESSION["id_usuario"]) ? "panel.php" : "login.php" ?>" class="btn boton">Volver al inicio</a>
</main>
<?php pie(); ?>
