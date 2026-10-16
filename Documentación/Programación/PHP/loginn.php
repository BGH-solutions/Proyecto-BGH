<?php

//muestra los mensajes de error al aparecer una pantalla en blanco
error_reporting(E_ALL);
ini_set("display_errors", "1");
ini_set("display_startup_errors", "1");

//abrimos sesión
session_start();

//Un if por si ya está abierta una sesión, que lo lleve al panel dependiendo de su id
if (isset($_SESSION["id_usuario"])){
    header("Location: pannel.php");
    exit;
}


//resupera un mensaje de error de procesar.login, si no hay error, deja untexto vacio
$mensaje = $_SESSION["mensaje"] ?? "";

//lo borramos para que no le siga apareciendo el mismo error
unset($_SESSION["mensaje"]);

?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset = "UTF-8">
        <meta name = "viewport" content ="width=device-width, initial-scale=1.0">
        <title> Iniciar sesión | Hospital clínca </title>
    </head>
    <body>

<h1> Iniciar sesión</h1>

<?php if ($mensaje !== ""): ?>
    <p style= "color: pink;">
        <?=  htmlspecialchars($mensaje) ?>
    </p>
<?php endif; ?>

<form action="procesar_loginn.php" method = "POST">
    <label for ="email"> Correo electrónico: </label>
    <br>
    <input
    type="email"
    id="email"
    name="email"
    required
>
<br><br>

<label for="contrasena"> Contraseña: </label>
<br>
<input
type="password"
id="contrasena"
name="contrasena"
required
>

<br> <br>

<button type = "submit"> Ingresar </button>
</form>

    </body>
</html> 
