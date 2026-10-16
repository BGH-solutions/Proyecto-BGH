<?php
//conexionn.php sirve para crear la conexion con MYSQL

//datos del sevidor local de base de datos
$host = "localhost";
$base_datos="hospital_sistema";
$usuario="root";
$contrasena="";

//usamos try para intentar conectar a la base de datos
//si no se puede, se pasa autmaticamente al catch
try{
    //se crea conexión PDO con MYSQL
    $conexion= new PDO(
        "mysql:host=$host;dbname=$base_datos;charset=utf8mb4",
        $usuario, $contrasena
    );

    //los errores de SQL se muestran como excepciones
    //gracias a esto es más fácil detectar qué consulta produjo el problema
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}catch(PDOException $error){
    //si la conexion falla, se para el programa y muestra error
    die("Error de conexión: ". $error->getMessage());
}