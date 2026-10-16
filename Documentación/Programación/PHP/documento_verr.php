<?php
//este archivo muestra los datos de un documento en especifico
//incluyendo la categoría y el usuario de quién lo cargó

require_once "../../protegerr.php";
require_once "../../conexionn.php";

//acá obtiene el id desde la url
//como documento_verr.php?id=1

$id_documento = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);

//valida que el ID sea correcto
if ($id_documento === false || $id_documento ===null){
    http_response_code(400);
    exit("El identificador del documento no es correcto");
}

//busca el documento y sus datos relacionados
$sql = "
SELECT
d.id_documento,
d.titulo,
d.descripcion,
d.archivo,
d.fecha_subida,
d.estado,
d.visibilidad,
c.nombre_categoria,
CONCAT(u.nombre, ' ', u.apellido) AS usuario_cargador
FROM documentos AS d
INNER JOIN categorias_documentos AS c
ON d.id_categoria = c.id_categoria
INNER JOIN usuarios AS u ON d.id_usuario = u.id_usuario
WHERE d.id_documento = :id_documento
LIMIT 1
";

$consulta = $conexion->prepare($sql);
$consulta->execute([":id_documento"=>$id_documento]);

$documento = $consulta->fetch(PDO::FETCH_ASSOC);

//y si no se encontró ningún documento, muestra error
if(!$documento){
    http_response_code(404);
    exit("No se encontró el documento");
}
?>

<!DOCTYPE html>
<html lang="es">
    <head> <meta charset="UTF-8">
    <title><?= htmlspecialchars($documento["titulo"]) ?></title>
    <body>
        <p> <strong> Categoría:</strong>
        <?= htmlspecialchars($documento["nombre_categoria"]) ?> </p>
        <p><strong> Descripcion: </strong>
        <?= nl2br(htmlspecialchars($documento["descripcion"]??"")) ?></p>
        <p> <strong> Archivo: </strong>
          <?= htmlspecialchars($documento["archivo"]) ?></p>
         <p> <strong> Subido por:</strong>
        <?= htmlspecialchars($documento["usuario_cargador"]) ?>    </p>
      <p> <strong> Fecha de subida:</strong>
        <?= htmlspecialchars($documento["fecha_subida"]) ?> </p>
  
  
        <!-- muestra el estado del documento -->
         <p> <strong> Estado: </strong>
         <?php if ((int) $documento ["estado"]===1): ?>
            activo
            <?php else: ?>
                inactivo
                <?php endif;?>
            </p>

            <p> <strong> Visibilidad: </strong>
            <?= htmlspecialchars($documento["visibilidad"]) ?>
            </p>
            <a href="documentoss.php"> Volver al listado </a>
  
    </body>
</html>