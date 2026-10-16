<?php
session_start();

require_once "conexionn.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: registroo.php");
    exit;
}

$nombre = trim($_POST["nombre"] ?? "");
$apellido = trim($_POST["apellido"] ?? "");
$email = trim($_POST["email"] ?? "");
$contrasena = $_POST["contrasena"] ?? "";

if ($nombre === "" || $apellido === "" || $email === "" || $contrasena === "") {
    $_SESSION["mensaje"] = "Todos los campos son obligatorios.";

    $_SESSION["registro_nombre"] = $nombre;
    $_SESSION["registro_apellido"] = $apellido;
    $_SESSION["registro_email"] = $email;

    header("Location: registroo.php");
    exit;
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
    "id_rol" => 1,
    "nombre" => $nombre,
    "apellido" => $apellido,
    "email" => $email,
    "contrasena" => $hash
]);

header("Location: loginn.php");
exit;