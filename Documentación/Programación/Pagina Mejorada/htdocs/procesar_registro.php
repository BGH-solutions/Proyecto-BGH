<?php
session_start();

require_once __DIR__ . "/config.php";
require_once __DIR__ . "/conexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: registro.php");
    exit;
}

$nombre = trim($_POST["nombre"] ?? "");
$apellido = trim($_POST["apellido"] ?? "");
$email = trim($_POST["email"] ?? "");
$contrasena = $_POST["contrasena"] ?? "";

// vuelve al formulario con un mensaje, conservando lo que escribió
function volver_con_error(string $mensaje, string $nombre, string $apellido, string $email): void
{
    $_SESSION["mensaje"] = $mensaje;
    $_SESSION["registro_nombre"] = $nombre;
    $_SESSION["registro_apellido"] = $apellido;
    $_SESSION["registro_email"] = $email;
    header("Location: registro.php");
    exit;
}

if ($nombre === "" || $apellido === "" || $email === "" || $contrasena === "") {
    volver_con_error("Todos los campos son obligatorios.", $nombre, $apellido, $email);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    volver_con_error("El correo electrónico no es válido.", $nombre, $apellido, $email);
}

if (strlen($contrasena) < 8) {
    volver_con_error("La contraseña debe tener al menos 8 caracteres.", $nombre, $apellido, $email);
}

// ¿ya existe ese correo?
$existe = $conexion->prepare("SELECT 1 FROM usuarios WHERE email = :email LIMIT 1");
$existe->execute([":email" => $email]);
if ($existe->fetch()) {
    volver_con_error("Ya existe una cuenta con ese correo.", $nombre, $apellido, $email);
}

$hash = password_hash($contrasena, PASSWORD_DEFAULT);

$sql = "
    INSERT INTO usuarios
    (id_rol, nombre, apellido, email, contrasena)
    VALUES
    (:id_rol, :nombre, :apellido, :email, :contrasena)
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    "id_rol" => ROL_PACIENTE,
    "nombre" => $nombre,
    "apellido" => $apellido,
    "email" => $email,
    "contrasena" => $hash
]);

$_SESSION["exito"] = "Cuenta creada. Ya podés iniciar sesión.";
header("Location: login.php");
exit;
