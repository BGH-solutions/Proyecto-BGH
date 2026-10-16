<?php

//se carga la conexion desde la carpeta superior

require_once __DIR__ . "/../conexionn.php";

//acá lee el identificador enviado en la url
$id_encuesta = filter_input(
    INPUT_GET,
    "id_encuesta",
    FILTER_VALIDATE_INT
);

//si no se indica alguna encuesta, se usa el numero 1
if ($id_encuesta === null) {
    $id_encuesta = 1;
}

//if para los identificadores invalidos
if ($id_encuesta === false || $id_encuesta <1){
    http_response_code(400);
    exit("El identificador de la encuesta no es válido");
}

//se busca una encuesta activa dentro del periodod de disponibilidad
//cada parametro se pasa por separado
$consulta_encuesta = $conexion -> prepare ("
SELECT
id_encuesta,
titulo,
descripcion
FROM encuestas
WHERE id_encuesta = :id_encuesta
AND estado =1
AND fecha_inicio <= CURRENT_DATE()
AND(
fecha_fin IS NULL
OR fecha_fin >= CURRENT_DATE()
)
LIMIT 1
");

$consulta_encuesta->execute([
    "id_encuesta" => $id_encuesta
]);
//se obtiene la fila como un arreglo con nombres de las columnas
$encuesta = $consulta_encuesta ->fetch(PDO::FETCH_ASSOC);

if(!$encuesta) {
    http_response_code(404);
    exit("La encuesta no exite o no está activa");
}

//acá se obtiene los servicios disponibles para el menu desplegable
$consulta_servicios = $conexion -> query("
SELECT id_servicio, nombre_servicio
FROM servicios
WHERE estado =1
ORDER BY nombre_servicio ASC
");

$servicios = $consulta_servicios->fetchAll(PDO::FETCH_ASSOC);
//acá se permite dejar un servicio seleccionado desde la url (servicio=2)
$servicio_preseleccionado = filter_input(
    INPUT_GET,
    "servicio",
    FILTER_VALIDATE_INT
);
//cada clave será el atributo name de una pregunta
//deben coinicidr el nombre que lee guardar_encuesta
$preguntas = [
    "atencion_personal" =>
    "¿Cómo calificarías la atención del personal?",

    "tiempo_de_espera" =>
    "¿Cómo calificarías el tiempo de espera?",

    "limpieza" =>
    "¿Cómo calificarías la limpieza del lugar?",

    "satisfaccion_general"=>
    "¿Cómo calificarías tu satisfacción general?
    "];

    //se convierten los caracteres especiales antes de mostrar texto en html
    function escapar($texto): string
    {
        return htmlspecialchars(
            (string) $texto,
            ENT_QUOTES | ENT_SUBSTITUTE,
            "UTF-8"
        );
    }

    ?>
    <!DOCTYPE html>
    <html lang ="es">
        <head> 
            <meta charset="UTF-8">
            <meta name="viewport"
            content ="width=device-width, initial-scale=1">

            <title><?= escapar($encuesta["titulo"]) ?></title>

            <style>
            body {
font-family: Arial, sans-serif;
background: skyblue;
color: black;
margin: 0;
padding: 20px;
                }

                main{
                    max-width: 650;
                    margin: 0 auto;
                    padding: 24px;
                    background: white;
                    border-radius: 12px;
                }

                h1{ color: pink;
            }
            .aviso{
                background: red;
                padding: 14px;
                border-radius: 8px;
                line-height: 1.5;
            }

            select,
            textarea {
                box-sizing: border-box;
                width: 100%;
                margin-top: 8px;
                padding: 10px;
                font: inherit;
            }

            fieldset{
                margin:24px 0;
                padding: 16px;
                border: 1px solid gray;
                border-radius: 8px;
            }

            legend {
                padding:0 6px;
            }

            .opcion {
                display: block;
                padding: 8px 0;
            }

            button {
                width:100%;
                margin-top: 20px;
                padding: 14;
                border: none;
                border-radius: 8px;
                background: azure;
                color: white;
                font: inherit;
                cursor: pointer;
            }
button:hover{
    background: black;
}

            </style>
            </head>
                
                            <style> 
                            .btn-enviar{
background-color: #FFB5C0;
color:black;
border: none;
border-radius: 10px;
padding: 12px 28px;
margin-top: 20px;
font-family: inherit;
font-size: 16px;
font-weight: 600;
cursor: pointer;
box-shadow: 0 4px 10px rgba(31, 89, 201, 0.2s);
transition: background-color 0.2s ease,
box-shadow 0.2 ease;
                                            }
                                            .btn-enviar:hover{
                                                background-color: cornflowerblue;
                                                box-shadow: 0 6px 14px rgba(31, 89, 201, 0.3);
                            }

                            .btn-enviar:active{
                                background-color: teal;
                            }

                            .btn-enviar:focus-visible{
outline: 3px solid lawngreen;
outline-offset: 4px;
                                            
                            }
</style>
            <body><main>
                <!-- Mostramos los datos de la encuesta consultada -->

            <h1><?=  escapar($encuesta["titulo"]) ?></h1>

            <?php if (!empty($encuesta["descripcion"])):?>
                <p><?=  escapar($encuesta["descripcion"]) ?></p>
                <?php endif;?>

                <p class="aviso">
                    Encuestas anonimas.
                    No necesitás iniciar sesion, no guardamos nombre
                    ni cédula o el correo.
                    No pongas información personal en los comentarios
                </p>

                <?php if (count($servicios) ===0): ?>

                    <p>No hay servicios para la encuesta en estos momentos</p>

                    <?php else: ?>
<!-- post envia los datos del archivo encargado de guardarlos-->
                        <form action ="guardar_encuestaa.php" method="POST">

                        <!-- el identificador va con las respuestas -->
                        <input
                        type="hidden"
                        name="id_encuesta"
                        value="<?= (int)$encuesta["id_encuesta"] ?>"
                        >

                        <label for="id_servicio"> ¿Qué servicio del hospital usaste?</label>

                        <select 
                        name="id_servicio"
                        id="id_servicio"
                        required>

                        <option value="">Seleccione un servicio </option>

                        <?php foreach($servicios as $servicio): ?>
                            <option 
                            value="<?=  (int) $servicio["id_servicio"] ?>"
                            <?= (
                                $servicio_preseleccionado === (int) $servicio["id_servicio"]
                             ) ? "selected" : "" ?>
                           >
                        <?= escapar ($servicio["nombre_servicio"]) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>

                    <p> Calificá cada aspecto del 1 al 5 (1 peor, 5 mejor)
                        </p>
<!-- se genera una pregunta por cada elemento del arreglo-->
                        <?php foreach ($preguntas as $campo =>$texto): ?>
                            <fieldset>
                                <legend><?=  escapar($texto) ?></legend>
                                <!-- acá se generan las cinco opciones de esa pregunta-->
                                <?php for ($valor = 1; $valor <= 5; $valor++): ?>
                                    <label class="opcion">
                                        <input 
                                        type= "radio"
                                        name="<?= escapar($campo)?>"
                                        value="<?= $valor ?>"
                                        required
                                         >

                                        <?=  $valor ?>
                                        <?php if ($valor ===1):?>
                                            - Peor valoración
                                            <?php elseif ($valor ===5): ?>
                                                - Mejor valoración
                                                <?php endif;?>
                                    </label>
                                    <?php endfor;?>
                            </fieldset>
                            <?php endforeach;?>

                            <label for="comentario">
                                ¿Qué podríamos mejorar? (opcional)
                            </label>

                            <textarea
                            name= "comentario"
                            id="comentario"
                            rows="5"
                            maxlenght ="200"
                            placeholder ="Hasta 200 caracteres, no ponga datos personales"
                            ></textarea>
                            <button type="submit" class="btn-enviar">
                                Enviar
                            
                            <style> 
                            .btn-enviar{
background-color: #FFB5C0;
color:black;
border: none;
border-radius: 10px;
padding: 12px 28px;
margin-top: 20px;
font-family: inherit;
font-size: 16px;
font-weight: 600;
cursor: pointer;
box-shadow: 0 4px 10px rgba(31, 89, 201, 0.2s);
transition: background-color 0.2s ease,
box-shadow 0.2 ease;
                                            }
                                            .btn-enviar:hover{
                                                background-color: cornflowerblue;
                                                box-shadow: 0 6px 14px rgba(31, 89, 201, 0.3);
                            }

                            .btn-enviar:active{
                                background-color: teal;
                            }

                            .btn-enviar:focus-visible{
outline: 3px solid lawngreen;
outline-offset: 4px;
                                            
                            }
</style>
                            </button>
                        </form>
                        <?php endif; ?>


            </main></body>
    </html>