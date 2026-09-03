<?php

// esta página solo va a verla el administrador , o sea, alguien con rol 4

// inicia la sesión para poder confirmar qué persona está conectada
session_start();

// si no existe una sesión, se se envia al login.
// ../ porque login.php está una carpeta más arriba
if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../login.php");
    exit;
}

// comprueba que el rol sea administrador, o sea, que el id_rol sea 4
if ((int) $_SESSION["id_rol"] !== 4) {

    // código HTTP 403 acceso prohibido
    http_response_code(403);

    // detiene el archivo y muestra un mensaje
    exit("no tenés permiso para acceder a este panel.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title> Administración | Hospital de clínica</title>
</head>
<body>

    <h1> Panel de administración</h1>

    <p>
        sesión iniciada como:
        <?= htmlspecialchars($_SESSION["nombre"]) ?>
        <?= htmlspecialchars($_SESSION["apellido"]) ?>
    </p>

    <p>
        Rol:
        <?= htmlspecialchars($_SESSION["nombre_rol"]) ?>
    </p>

    <h2> opciones de administración</h2>

    <ul>
        <li> Gestionar usuarios</li>
        <li> Gestionar categorías de documentos</li>
        <li> Gestionar documentos</li>
        <li> Gestionar encuestas</li>
    </ul>

    <!-- ../ permite subir una carpeta desde admin/ hacia la raíz del proyecto -->
    <a href="../panel.php"> Ir al panel general</a>

    <br><br>

    <a href="../cerrar_sesion.php"> cerrar sesión</a>

</body>
</html>
