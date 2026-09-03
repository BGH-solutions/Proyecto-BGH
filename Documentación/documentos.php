<?php

//primero comprobamos que la persona haya iniciado sesión
require_once "proteger.php";

//conexion con la base de datos
require_once "conexion.php";

//acá se consulta los documentos con su categoría, el estado, la visibilidad
//y el usuario que los cargó
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
    INNER JOIN usuarios AS u
        ON d.id_usuario = u.id_usuario
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
    <title> Documentos | Hospital de clínica</title>
</head>
<body>

    <h1> repositorio de documentos</h1>

    <p>
        usuario conectado:
        <?= htmlspecialchars($_SESSION["nombre"]) ?>
    </p>

    <!-- enlace para crear un documento -->
    <?php if ((int) $_SESSION["id_rol"] === 4): ?>
        <a href="documento_nuevo.php">Agregar documento</a>
        <br><br>
    <?php endif; ?>

    <?php if (count($documentos) === 0): ?>

        <!-- estado vacío si todavía no hay documentos -->
        <p> No hay documentos subidos </p>

    <?php else: ?>

        <table border="1" cellpadding="8">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Categoría</th>
                    <th>Archivo</th>
                    <th>Visibilidad</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
//recorre todos los documentos y los muestra en la tabla
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

                        <td>
                            <?= htmlspecialchars($documento["archivo"]) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($documento["visibilidad"]) ?>
                        </td>

                        <td>
                            <?php if ((int) $documento["estado"] === 1): ?>
                                Activo
                            <?php else: ?>
                                Inactivo
                            <?php endif; ?>
                        </td>

                        <td>
                            <!-- vista individual mediante id_documento -->
                            <a href="documento_ver.php?id=<?= (int) $documento["id_documento"] ?>">
                                Ver
                            </a>
//si el rol es 4 que corresponde al administrador, se le permite editar y eliminar
                            <?php if ((int) $_SESSION["id_rol"] === 4): ?>
                                |
                                <a href="documento_editar.php?id=<?= (int) $documento["id_documento"] ?>">
                                    editar
                                </a>

                                |
                                <a
                                    href="documento_eliminar.php?id=<?= (int) $documento["id_documento"] ?>"
                                    onclick="return confirm('¿Seguro que querés eliminar este documento?');"
                                >
                                    eliminar
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php endif; ?>

    <br>

    <a href="panel.php"> Volver al panel</a> |
    <a href="cerrar_sesion.php"> Cerrar sesión</a>

</body>
</html>
