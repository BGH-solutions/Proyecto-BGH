<?php

// este archivo se pone al principio de todas las páginas privadas para que
// nadie pueda acceder a ellas sin iniciar sesión primero

// abre la sesión para poder ver $_SESSION.
session_start();

// si no existe el dato id_usuario, significa que nadie inició sesión
if (!isset($_SESSION["id_usuario"])) {

    // guarda un mensaje que se muestra al regresar al login
    $_SESSION["mensaje"] = "tenés que iniciar sesión para acceder";

    // vuelve a llevar al login.
    header("Location: login.php");
    exit;
}