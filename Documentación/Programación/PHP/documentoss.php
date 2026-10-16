<?php

//comprobar que haya iniciado sesiòn
require_once "../../protegerr.php";
require_once "../../conexionn.php";

if(!isset($_SESSION["id_usuario"])){
  header("Location: loginn.php");
}

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
CONCAT (u.nombre, ' ', u.apellido) AS usuario_cargador
FROM documentos AS d
INNER JOIN categorias_documentos AS c
ON d.id_categoria = c.id_categoria
INNER JOIN usuarios AS u
ON
d.id_usuario = u.id_usuario
ORDER BY d.id_documento DESC
";

//ejecuta la consulta y guarda el resultado en $consulta
$consulta = $conexion->query($sql);

//obtiene todos los documentos encontrados
$documentos = $consulta->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title> Documentos | Hospital de clínica </title>
</head>
<body>
    <h1> Repositorio de documentos </h1>
    <p>Usuario conectado: <?=  htmlspecialchars($_SESSION["nombre"]) ?></p>

    <!-- enlace para crear documento -->
     <?php if ((int) $_SESSION["id_rol"] ===4): ?>
        <a href="documento_nuevoo.php"> Agregar documento </a>
        <br><br>
        <?php endif;?>
        <?php if(count($documentos) === 0): ?>
            <p> No hay documentos subidos </p>
            <?php else: ?>
                <table border="1" cellpadding="8">
                    <thead>
                        <tr>
                            <th> ID </th>
                            <th>Título</th>
                            <th>Categoria </th>
                            <th>Archivo</th>
                            <th>Visibilidad</th>
                            <th>Estado</th>
                            <th>Acciones</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documentos as $documento): ?>
                            <tr>
                                <td>
                                    <?= (int) $documento["id_documento"] ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($documento["titulo"]) ?>
                                </td>
                                <td>
                                     <?= htmlspecialchars($documento["nombre_categoria"]) ?>

                                </td>
                                <td> <?= htmlspecialchars($documento["archivo"]) ?></td>
                                <td> <?= htmlspecialchars($documento["visibilidad"]) ?></td>
                                <td><?php if ((int)$documento["estado"]===1): ?>
                                activo
                                <?php else: ?>
                                    inactivo
                                    <?php endif; ?>
                                </td>

                                <td> <!-- vista individual mediante id_documento-->
                                    <a href="documento_verr.php?id=<?= (int)$documento["id_documento"] ?>">
                                        Ver
                                    </a>
                                    <?php if((int) $_SESSION["id_rol"]===4):?>
                                    <a href="documento_editarr.php?id=<?= (int)$documento["id_documento"] ?>">
                                        editar
                                    </a>
                                    <a href="documento_eliminarr.php?id=<?= (int)$documento["id_documento"] ?>">
                                        eliminar
                                    </a>
                                    <?php endif;?>
                                </td>
                            </tr>
                            <?php endforeach;?>
                    </tbody>
                </table>
                <?php endif; ?>
                <br>
<a href="../../pannel.php"> Volver al panel </a>
<a href="../../cerrar_sesionn.php"> Cerrar sesión </a>

</body>

</html>