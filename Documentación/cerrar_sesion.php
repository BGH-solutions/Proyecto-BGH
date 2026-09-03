<?php

// elimina los datos guardados en $_SESSION, destruye la sesión y te lleva
//de nuevo al login, session_destroy() borra los datos de sesión del servidor


// inicia la sesión actual para poder borrarla
session_start();

// vacía el arreglo $_SESSION.
// o sea borra id_usuario, id_rol, nombre, email, etc
$_SESSION = [];

//  si php está usando cookies la sesión
// suele ser que sí, por eso también se eliminan esas cookies, 
// para que no quede rastro de la sesión anterior
if (ini_get("session.use_cookies")) {

    // obtiene las configuraciones de la cookie de sesión actua
    $parametros = session_get_cookie_params();

    // vuelve a crear la cookie con una fecha pasada
// al estar vencida, el navegador la elimina
    setcookie(
        session_name(),          // nombre de la cookie de sesión
        "",                      // valor vacío
        time() - 42000,          // fecha en el pasado, queda vencida
        $parametros["path"],     // misma ruta de la cookie original
        $parametros["domain"],   // mismo dominio
        $parametros["secure"],   // mantiene la configuración de seguridad
        $parametros["httponly"]  // mantiene la configuración HttpOnly
    );
}

// elimina en el servidor todos los datos asociados a la sesión
session_destroy();

// luego de cerrar sesión, dte vuelve a enviar a la pantalla de login
header("Location: login.php");
exit;
