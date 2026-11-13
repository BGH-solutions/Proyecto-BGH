<?php
// Se incluye al principio de toda página que requiera sesión iniciada.
session_start();

require_once __DIR__ . "/config.php";
require_once __DIR__ . "/includes/funciones.php";

if (!isset($_SESSION["id_usuario"])) {
    $_SESSION["mensaje"] = "Debe iniciar sesión para acceder";
    header("Location: " . BASE_URL . "/login.php");
    exit;
}

// Consulta el rol y el estado vigentes, incluso si la sesión ya estaba abierta.
require_once __DIR__ . "/conexion.php";
$cuenta = $conexion->prepare("
    SELECT u.id_rol, r.nombre_rol
    FROM usuarios AS u
    JOIN roles AS r ON r.id_rol = u.id_rol
    WHERE u.id_usuario = :usuario AND u.estado = 1
");
$cuenta->execute([":usuario" => $_SESSION["id_usuario"]]);
$rol_vigente = $cuenta->fetch(PDO::FETCH_ASSOC);
if (!$rol_vigente) {
    $_SESSION = ["mensaje" => "Tu cuenta no está disponible."];
    header("Location: " . BASE_URL . "/login.php");
    exit;
}

$_SESSION["id_rol"] = (int) $rol_vigente["id_rol"];
$_SESSION["nombre_rol"] = $rol_vigente["nombre_rol"];
