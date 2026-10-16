
<?php
//archivo para el panel general para cualquier persona que haya iniciado sesion 
//si una persona abre sesion si loguearse, el archivo proteger lo lleva al login


require_once "protegerr.php";
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title> Sector principal | Hospital de Clìnica </title>
</head>
<body>

<h1>
    Bienvenido
    <?= htmlspecialchars($_SESSION["nombre"]) ?>
</h1>

<!-- para mostrar el rol de la persona conectada -->

<p>
    Rol: 
    <?= htmlspecialchars($_SESSION["nombre_rol"]) ?>
</p>

<p>
En este sector se va a poder acceder a los documentos, informaciòn 
de los servicios y las encuestas màs adelante.
</p>

<!-- el enlace que ejecuta el archivo para cerrar la sesiòn -->
 <a href= "cerrar_sesionn.php"> Cerrar sesiòn  </a>
</body>
</html>