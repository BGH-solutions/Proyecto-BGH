<?php

//este archivo se pone al principio en todas las paginas para que 
//nadie acceda sin iniciar sesión primero

//abrir la sesion

session_start();

if(!isset($_SESSION["id_usuario"])){
$_SESSION["mensaje"] = "Debe iniciar sesión para acceder";

//lo lleva al login

header("Location: loginn.php");
exit;
}
