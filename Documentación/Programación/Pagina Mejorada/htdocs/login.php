<?php
session_start();
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/includes/funciones.php";

// si ya hay sesión abierta, va directo al panel
if (isset($_SESSION["id_usuario"])) {
    header("Location: panel.php");
    exit;
}

// recupera (y borra) el mensaje de error o de éxito
$mensaje = $_SESSION["mensaje"] ?? "";
$exito = $_SESSION["exito"] ?? "";
unset($_SESSION["mensaje"], $_SESSION["exito"]);

encabezado("Inicio de sesión", "", true);
?>
<main class="login-main">
    <form class="login-card" action="procesar_login.php" method="POST">
        <h2 class="titulo">Inicio de sesión</h2>

        <label for="email">Correo electrónico</label>
        <input type="email" id="email" name="email" autocomplete="username" required>

        <label for="contrasena">Contraseña</label>
        <input type="password" id="contrasena" name="contrasena" autocomplete="current-password" required>

        <?php if ($mensaje !== ""): ?>
            <p class="error"><?= e($mensaje) ?></p>
        <?php endif; ?>
        <?php if ($exito !== ""): ?>
            <p class="text-success text-center m-0"><?= e($exito) ?></p>
        <?php endif; ?>

        <button type="submit" class="btn-ingresar">Ingresar</button>
        <p class="text-center mt-4 mb-0"><a href="registro.php">Crear cuenta</a></p>
        <p class="text-center mt-2 mb-0"><a href="publico/index.php">Documentos para pacientes</a></p>
    </form>
</main>
<?php pie(); ?>
