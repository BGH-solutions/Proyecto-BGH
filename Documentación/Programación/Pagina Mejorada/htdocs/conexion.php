<?php
// Copiá estos cuatro datos del panel MySQL de tu hosting.
$host = "TU_HOST_MYSQL";
$usuario = "TU_USUARIO_MYSQL";
$contrasena = "TU_CONTRASENA_MYSQL";
$base_datos = "TU_BASE_MYSQL";

try {
    $conexion = new PDO(
        "mysql:host=$host;dbname=$base_datos;charset=utf8mb4",
        $usuario,
        $contrasena,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $error) {
    error_log("Error al conectar con la base de datos: " . $error->getCode());
    http_response_code(500);
    exit("No se pudo conectar con la base de datos.");
}
