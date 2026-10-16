<?php

//este archivo solo va a verlo el admin

//inicia la sesión 
session_start();

//si no existe una sesión, se envia al login
// ../ porque login está yba carpeta más arriba
if(!isset($_SESSION["id_usuario"])){
    header("Location:../loginn.php");
    exit;
}

if((int)$_SESSION["id_rol"] !== 4){

// código http 403 acceso prhibido
http_response_code(403);
//detiene el archivo y le muestra el mensaje
exit("no tenés permiso para acceder a este panel");
}

?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title> Administración | Hospital de clínica </title>
</head>

<body>
    <h1> Panel de administración </h1>

    <p> Sesión iniciada como:
        <?=htmlspecialchars($_SESSION["nombre"]) ?>
        <?=htmlspecialchars($_SESSION["apellido"]) ?>
</p>

<p> Rol: <?=htmlspecialchars($_SESSION["nombre_rol"])?>
</p>

<h2> Opciones de administración </h2>

<ul> 
    <li> Gestionar usuarios </li>
    <li> Gestionar categoriías de documentos </li>
    <li><a href="../admin/documentos/documentoss.php"> Gestionar documentos </a> </li>
    <li> Gestionar encuestas </li>
</ul>

<a href = "../pannel.php"> Ir al panel general <a>
    <br><br>

    <a href="../cerrar_sesionn.php"> cerrar sesión </a>

</body>
</html>