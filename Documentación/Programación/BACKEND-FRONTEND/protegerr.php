<?php
// Se incluye al principio de toda página que requiera sesión iniciada.
session_start();

require_once __DIR__ . "/config.php";
require_once __DIR__ . "/includes/funciones.php";

if (!isset($_SESSION["id_usuario"])) {
    $_SESSION["mensaje"] = "Debe iniciar sesión para acceder";
    header("Location: " . BASE_URL . "/loginn.php");
    exit;
}
