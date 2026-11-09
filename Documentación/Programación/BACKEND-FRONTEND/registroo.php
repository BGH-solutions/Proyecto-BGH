<?php
session_start();
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/includes/funciones.php";

// datos y mensaje que dejó procesar_registroo.php si hubo un error
$nombre = $_SESSION["registro_nombre"] ?? "";
$apellido = $_SESSION["registro_apellido"] ?? "";
$email = $_SESSION["registro_email"] ?? "";
$mensaje = $_SESSION["mensaje"] ?? "";
unset($_SESSION["registro_nombre"], $_SESSION["registro_apellido"], $_SESSION["registro_email"], $_SESSION["mensaje"]);

encabezado("Crear cuenta", "", true);
?>
<main class="login-main">
    <form class="login-card" action="procesar_registroo.php" method="POST">
        <h2 class="titulo">Crear cuenta</h2>

        <label for="nombre">Nombre</label>
        <input type="text" id="nombre" name="nombre" value="<?= e($nombre) ?>" required>

        <label for="apellido">Apellido</label>
        <input type="text" id="apellido" name="apellido" value="<?= e($apellido) ?>" required>

        <label for="email">Correo electrónico</label>
        <input type="email" id="email" name="email" value="<?= e($email) ?>" required>

        <label for="contrasena">Contraseña</label>
        <input type="password" id="contrasena" name="contrasena" minlength="8" autocomplete="new-password" required>

        <?php if ($mensaje !== ""): ?>
            <p class="error"><?= e($mensaje) ?></p>
        <?php endif; ?>

        <button type="submit" class="btn-ingresar">Registrarse</button>
        <p class="text-center mt-4 mb-0"><a href="loginn.php">Ya tengo cuenta</a></p>
    </form>
</main>
<?php pie(); ?>
