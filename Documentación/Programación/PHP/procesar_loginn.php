<?php

// este archivo recibe los datos del formulario, busca el usuario en la base 
// y valida la contraseña y guarda la información necesaria dentro de $_SESSION

// inicia la sesión para poder guardar datos en $_SESSION
session_start();

// incluye la conexión a la base de datos
// require_once evita incluir el mismo archivo más de una vez
require_once "conexionn.php";

// verifica que este archivo haya sido abierto desde el formulario con POST
// si alguien intenta abrir procesar_login.php directamente por URL,
// se lo devuelve al login.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: loginn.php");
    exit;
}

// obtiene el email enviado en el formulario
// trim elimina espacios al inicio o al final
// ?? "" evita errores si el campo no llegó
$email = trim($_POST["email"] ?? "");

// obtiene la contraseña enviada
// acá no ponemos trim a una contraseña ya que puede poner un espacio
// como parte de su contraseña, y no queremos eliminarlo
$contrasena_ingresada = $_POST["contrasena"] ?? "";

// comprueba que ambos campos hayan sido completados
if ($email === "" || $contrasena_ingresada === "") {
    $_SESSION["mensaje"] = "Completá el correo y la contraseña.";
    header("Location: loginn.php");
    exit;
}

// consulta SQL para buscar el usuario por su email.
// INNER JOIN permite también recuperar el nombre de su rol
// :email es un parámetro seguro que evitá inyección SQL
$sql = "
    SELECT
        u.id_usuario,
        u.id_rol,
        u.nombre,
        u.apellido,
        u.email,
        u.contrasena,
        u.estado,
        r.nombre_rol
    FROM usuarios AS u
    INNER JOIN roles AS r
        ON u.id_rol = r.id_rol
    WHERE u.email = :email
    LIMIT 1
";

// sirve para prepara la consulta antes de ejecutarla
$consulta = $conexion->prepare($sql);

// reemplaza :email por el correo real de la persona.
$consulta->execute([
    ":email" => $email
]);

// da una sola fila del resultado.
// si no existe ese correo, $usuario tendrá el valor false.
$usuario = $consulta->fetch(PDO::FETCH_ASSOC);

// comprueba que exista el usuario, que esté activo
// y que la contraseña ingresada coincida con el hash guardado
if (
    !$usuario ||
    (int) $usuario["estado"] !== 1 ||
    !password_verify($contrasena_ingresada, $usuario["contrasena"])
) {
    $_SESSION["mensaje"] = "Correo, contraseña incorrectos o usuario inactivo.";
    header("Location: loginn.php");
    exit;
}
// regenera el ID de sesión luego de iniciar sesión correctamente
// evita mantener el identificador de sesión anterior
session_regenerate_id(true);

// guarda en la sesión los datos necesarios mientras el usuario navega
// no guardamos la contraseña en la sesión
$_SESSION["id_usuario"] = $usuario["id_usuario"];
$_SESSION["id_rol"] = $usuario["id_rol"];
$_SESSION["nombre"] = $usuario["nombre"];
$_SESSION["apellido"] = $usuario["apellido"];
$_SESSION["email"] = $usuario["email"];
$_SESSION["nombre_rol"] = $usuario["nombre_rol"];

// si el rol es 4, corresponde al administrador
// lo llevamos al panel para administradores
if ((int) $usuario["id_rol"] === 4) {
    header("Location: admin/panel.adminn.php");
    exit;
}

// cualquier otro rol va para el panel general
header("Location: pannel.php");
exit;