<?php

// este es el panel general para cualquier persona que haya iniciado sesión
// si una persona intenta abrirsin sesión, nuestro archivo proteger.php lo envía al login

// verifica que exista una sesión activa y
// si no existe, envia automáticamente al login
require_once "proteger.php";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title> Sector principal | Hospital de Clinica </title>
</head>
<body>

    <h1>
        Bienvenido,
        <?= htmlspecialchars($_SESSION["nombre"]) ?>
    </h1>

    <!-- muestra el rol de la persona conectada -->
    <p>
        Rol:
        <?= htmlspecialchars($_SESSION["nombre_rol"]) ?>
    </p>

    <p>
        Desde este sector se podrá acceder a documentos,
        información de los servicios y encuestas.
    </p>

    <!-- enlace que ejecuta el archivo para cerrar la sesión -->
    <a href="cerrar_sesion.php"> Cerrar sesión</a>

</body>
</html>