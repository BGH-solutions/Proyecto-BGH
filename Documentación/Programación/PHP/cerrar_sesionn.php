<?php

// este archivo elimina datos guardados en $_SESSIOn, destruye la
// sesión y te lleva de nuevo al login, session_destroy() borra los datos de la sesión del servidor

// inicia la sesión actual para poder borrarla
session_start();

//vacia el arreglo $_SESSION, o sea, borra id_usuario, id_rol, etc
$_SESSION = [];

//si php está usando cookies también se eliminan esas cookies
//para que no quede rastro de la sesión anterior
if(ini_get("session.use_cookies")){
//obtiene las configs de la cookie de la sesió actual
$parametros= session_get_cookie_params();

//vuelve a crear la cookie con una fecha pasada
//al estar vencida, el navegador la elimina

setcookie(
session_name(),     //nombre de la cookie de sesiòn
"",                    //valor vacìo
time() - 42000,       //fecha en el pasado, queda vencida
$parametros["path"],  //misma ruta que la cookie original
$parametros["domain"], //mismo dominio
$parametros["secure"], //mantiene la configuración de seguridad
$parametros["httponly"] //mantiene la confi de httpOnly
);
}
//elimina en el servidor todos los datos relacionados a la sesión
session_destroy();
//luego de cerrar sesión, te vuelve a enviar a la pantalla de login
header("Location: loginn.php");
exit;

