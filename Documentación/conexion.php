<?php
//conexion.php sirve para crear la conexión con MySQL

// datos del servidor local de base de datos.
$host = "localhost";
$base_datos = "hospital_sistema";
$usuario = "root";
// en XAMPP el servidor es localhost, el usuario es root
$contrasena = "";
//la contraseña está vacía.

// try es para intentar conectarse a la base de datos
// y si pasa algo, se pasa automáticamente al catch
try {

    // Se crea una conexión PDO con MySQL
    $conexion = new PDO(
        "mysql:host=$host;dbname=$base_datos;charset=utf8mb4",
        // charset=utf8mb4 permite usar tildes, ñ y otros caracteres
        $usuario,
        $contrasena
    );

    //los errores de SQL se muestran como excepciones
    //gracias a esto es más fácildetectar qué consulta produjo el problema
    $conexion->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

} catch (PDOException $error) {

    // si la conexión falla, se para el programa y se muestra el error
    die("Error de conexión: " . $error->getMessage());
}