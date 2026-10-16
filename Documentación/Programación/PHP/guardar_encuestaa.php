<?php

//acá el archivo procesa envios post, si alguien abre
//directamente se envía al formulario
if($_SERVER["REQUEST_METHOD"]!== "POST"){
    header("Location: encuestaa.php");
    exit;
}

//cargamos kla conexion de la bd
require_once __DIR__ . "/../conexionn.php";
//pdo lanza excepciones nte errores de la bd
$conexion->setAttribute(
    PDO::ATTR_ERRMODE,
    PDO::ERRMODE_EXCEPTION
);

//lee un campo post y valida que sea entero dentro del rango
//devuelve un entero valido, y false si es navalido o null si no llegó
function recibirEntero(string $campo, int $minimo, int $maximo)
{
    return filter_input(
        INPUT_POST,
        $campo, 
        FILTER_VALIDATE_INT,
        [
            "options" => [
                "min_range" => $minimo,
                "max_range" => $maximo
            ]
        ]
    );
}
//identificadores, deben ser enteros positivos
$id_encuesta = recibirEntero("id_encuesta", 1, PHP_INT_MAX);
$id_servicio = recibirEntero("id_servicio", 1, PHP_INT_MAX);
//calificaciones, deben ser enteros entre 1 y 5
$atencion_personal = recibirEntero("atencion_personal",1,5);
$tiempo_de_espera = recibirEntero("tiempo_de_espera",1,5);
$limpieza = recibirEntero("limpieza",1,5);
$satisfaccion_general=recibirEntero("satisfaccion_general",1,5);
//si no hay ciomentarios, se usa una cadena vacia
$comentario_recibido=$_POST["comentario"]??"";
$comentario ="";
//acá se anulan los problemas encontrados
$errores =[];

if ($id_encuesta === false || $id_encuesta === null){
    $errores[] = "La encuesta seleccionada no es valida";
}

if ($id_servicio === false || $id_servicio === null){
    $errores[] = "Seleccione un servicio valido";
}
//se asciocia cada califiacion con su etiqueta, así permite informar
//bien qué pregunta tiene problema
$calificaciones = [
    $atencion_personal,
    $tiempo_de_espera,
    $limpieza,
    $satisfaccion_general
];

foreach ($calificaciones as $calificacion){
    if($calificacion === false || $calificacion === null){
        $errores[] = "Responde todas las preguntas con valor entre 1 y 5";
        break;
    
    }
}
//se rechaza un comentario enviado por arreglo o otro tipo no esperado
if(!is_string($comentario_recibido)){
    $errores[] = "El comentario no es valido";
}else {
    //se quita los espacios
    $comentario =trim($comentario_recibido);
//se valida tambipen en el servidor el limite del comentario
    if(mb_strlen($comentario, "UTF-8")>200){
        $errores[] = "El comentario no puede ser tan largo";
    }
}
//se consulta la bd solo si los datos basicos son validos
if (count($errores)===0){
    try {
        //volvemos a verificar la disponi de la encuesta
        //puede haber cambiado desde que se abrió el formulario
        $consulta_encuesta = $conexion->prepare("
        SELECT id_encuesta
        FROM encuestas
        WHERE id_encuesta = :id_encuesta
        AND estado = 1
        AND fecha_inicio <= CURRENT_DATE()
        
        LIMIT 1
        ");
//verificamos que el servicio exista y esté activo
        $consulta_encuesta->execute([
            "id_encuesta" => $id_encuesta
        ]);

        if (!$consulta_encuesta->fetch(PDO::FETCH_ASSOC)){
            $errores[]="La encuesta no existe o ya no está activa";
        }

        $consulta_servicio = $conexion->prepare("
        SELECT id_servicio
        FROM servicios
        WHERE id_servicio = :id_servicio
        AND estado =1
        LIMIT 1
        ");

        $consulta_servicio->execute ([
            "id_servicio" => $id_servicio
        ]);

        if(!$consulta_servicio->fetch(PDO::FETCH_ASSOC)){
$errores[] = "El servicio no existe o no está disponible";
}//guardamos unicamente si todas las verificaciones pasaron

if (count($errores) ===0){
    $sql = "
    INSERT INTO respuestas_encuestas(
    id_encuesta,
    id_servicio,
    atencion_personal,
    tiempo_de_espera,
    limpieza,
    satisfaccion_general,
    comentario
    )
    VALUES (
    :id_encuesta,
    :id_servicio,
    :atencion_personal,
    :tiempo_de_espera,
    :limpieza,
    :satisfaccion_general,
    :comentario
    )
    ";

    $consulta = $conexion->prepare($sql);
//se envian los valores por separado a la consulta sql
//el comentario vacio se guarda como cadena
    $consulta->execute([
        "id_encuesta" => $id_encuesta,
        "id_servicio" => $id_servicio,
        "atencion_personal" => $atencion_personal,
        "tiempo_de_espera" => $tiempo_de_espera,
        "limpieza" => $limpieza,
        "satisfaccion_general" => $satisfaccion_general,
        "comentario" => $comentario !== ""? $comentario : null
    ]);

    //despues de guardar, redirigimos a una pagina get
    
    header("Location: graciass.php", true, 303);
    exit;
}

    } catch (PDOException $e) {
        //registramos el detalle tecnico
        //no se muestra añ usuario
        error_log(
        "Error al guardar encuesta: " . $e->getMessage()
        );

        http_response_code(500);

        $errores[] = "No se logró guardar la encuesta. Intente más tarde";

    }
}
//si hubo un error, el problema corresponde al envio
if(http_response_code() !==500){
    http_response_code(400);
}

$url_volver="encuestaa.php";

if(is_int($id_encuesta) && $id_encuesta>0) {
    $url_volver.="?id_encuesta=".$id_encuesta;

    if(is_int($id_servicio)&& $id_servicio >0){
$url_volver.="&servicio=".$id_servicio;
    }
}

?>

<!DOCTYPE html>

<html lang="es">

<head> <meta charset="UTF-8">
<meta 
name ="viewport"
content="width=device-width, initial-scale=1"
>
<title> No se pudo enviar la encuesta </title>
</head>
<body>
<h1> No se pudo enviar la encuesta</h1>

<ul>
    <?php foreach($errores as $error): ?>
        <li>
            <?=  htmlspecialchars(
                $error,
                ENT_QUOTES | ENT_SUBSTITUTE,
                "UTF-8"
                ) ?>
        </li>
        <?php endforeach; ?>
</ul>

<a href="<?= htmlspecialchars(
    $url_volver,
    ENT_QUOTES | ENT_SUBSTITUTE,
    "UTF-8"
) ?>">
Volver a la encuesta
    </a>
    </body>
</html>