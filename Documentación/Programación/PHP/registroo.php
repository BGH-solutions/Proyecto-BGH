
<?php
session_start();
$nombre = $_POST["nombre"]??"";
$apellido = $_POST["apellido"]??"";
$email = $_POST["email"]??"";
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title> Registro</title> </head>
        <body>
            <h1> Crear cuenta </h1>
            <form action="procesar_registroo.php" method="post">
            
            <label> Nombre: </label>
            <input type="text" name="nombre" value="<?=htmlspecialchars($nombre) ?>" required>
            <br><br>
            
            <label> Apellido: </label>
            <input type="text" name="apellido" value="<?=htmlspecialchars($apellido)?>" required>
            <br><br>
            
            <label> Email:</label>
                <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
                <br><br>

                <label> Contraseña: </label>
                <input type="password" name="contrasena" required>
                <br><br>

                <button type="submit"> Registrarse </button>

            </form>

            <a href ="loginn.php"> Ya tengo cuenta </a>

        </body>

</html>

