<?php

// muestra los mensajess de error, al presenciar la pantalla en blanco
// la recomendación fue esta barra de código, que se coloca al principio de
// cada archivo PHP
error_reporting(E_ALL);
ini_set("display_errors", "1");
ini_set("display_startup_errors", "1");

// abre la sesión
session_start();

// si ya llega a existir una sesion va directo al panel
if (isset($_SESSION["id_usuario"])) {
    header("Location: panel.php");
    exit;
}

// recupera un mensaje de error enviado desde procesar_login.php.
// y si no hay deja un texto vacio
$mensaje = $_SESSION["mensaje"] ?? "";

// acá lo borra para que al darle refresh no vuelva a aparecer el mensaje de error
unset($_SESSION["mensaje"]);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Iniciar sesión | Hospital Clínica</title>
</head>
<body>

    <h1> Iniciar sesión</h1>

    <?php if ($mensaje !== ""): ?>
        <p style="color: red;">
            <?= htmlspecialchars($mensaje) ?>
        </p>
    <?php endif; ?>

    // campo para el correo 
    <form action="procesar_login.php" method="POST">
        <label for="email"> Correo electrónico:</label>
        <br>
        <input
            type="email"
            id="email"
            name="email"
            required
        >

        <br><br>
// campo para la contraseña
        <label for="contrasena">Contraseña:</label>
        <br>
        <input
            type="password"
            id="contrasena"
            name="contrasena"
            required
        >

        <br><br>

        <button type="submit">Ingresar</button>
    </form>

</body>
</html>