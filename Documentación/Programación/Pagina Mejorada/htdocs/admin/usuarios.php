<?php
// Gestión de usuarios: cambiar rol y activar/desactivar (solo administrador).
require_once __DIR__ . "/../proteger.php";
require_once __DIR__ . "/../conexion.php";
exigir_admin();

$roles = $conexion->query("SELECT id_rol, nombre_rol FROM roles ORDER BY id_rol")->fetchAll(PDO::FETCH_ASSOC);
$ids_rol = array_map("intval", array_column($roles, "id_rol"));

$mensaje = "";
$tipo = "success";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_validar();
    $id_usuario = filter_input(INPUT_POST, "id_usuario", FILTER_VALIDATE_INT);
    $id_rol = filter_input(INPUT_POST, "id_rol", FILTER_VALIDATE_INT);
    $estado = filter_input(INPUT_POST, "estado", FILTER_VALIDATE_INT);

    if ($id_usuario === false || $id_usuario === null || !in_array($id_rol, $ids_rol, true) || ($estado !== 0 && $estado !== 1)) {
        $mensaje = "Datos no válidos.";
        $tipo = "danger";
    } elseif ($id_usuario === (int) $_SESSION["id_usuario"]) {
        // evita que el administrador se quite el acceso a sí mismo
        $mensaje = "No podés modificar tu propio usuario.";
        $tipo = "danger";
    } else {
        $conexion->prepare("UPDATE usuarios SET id_rol = :rol, estado = :estado WHERE id_usuario = :id")
                 ->execute([":rol" => $id_rol, ":estado" => $estado, ":id" => $id_usuario]);
        $mensaje = "Usuario actualizado.";
    }
}

$usuarios = $conexion->query("
    SELECT id_usuario, nombre, apellido, email, id_rol, estado
    FROM usuarios ORDER BY apellido, nombre
")->fetchAll(PDO::FETCH_ASSOC);

encabezado("Usuarios", "");
?>
<main class="contenido">
    <h2 class="titulo">Usuarios</h2>

    <?php if ($mensaje !== ""): ?>
        <div class="alert alert-<?= $tipo ?>"><?= e($mensaje) ?></div>
    <?php endif; ?>

    <div class="tabla-container" style="min-height: 0;">
        <table class="tabla-documentos">
            <thead><tr><th>Nombre</th><th>Correo</th><th>Rol</th><th>Estado</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($usuarios as $u): ?>
                    <?php $propio = (int) $u["id_usuario"] === (int) $_SESSION["id_usuario"]; ?>
                    <?php $f = "f" . (int) $u["id_usuario"]; ?>
                    <tr>
                        <td><?= e($u["nombre"] . " " . $u["apellido"]) ?><?= $propio ? " (vos)" : "" ?></td>
                        <td><?= e($u["email"]) ?></td>
                        <td>
                            <select name="id_rol" form="<?= $f ?>" class="form-select form-select-sm" <?= $propio ? "disabled" : "" ?>>
                                <?php foreach ($roles as $r): ?>
                                    <option value="<?= (int) $r["id_rol"] ?>" <?= (int) $r["id_rol"] === (int) $u["id_rol"] ? "selected" : "" ?>>
                                        <?= e($r["nombre_rol"]) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <select name="estado" form="<?= $f ?>" class="form-select form-select-sm" <?= $propio ? "disabled" : "" ?>>
                                <option value="1" <?= (int) $u["estado"] === 1 ? "selected" : "" ?>>Activo</option>
                                <option value="0" <?= (int) $u["estado"] === 0 ? "selected" : "" ?>>Inactivo</option>
                            </select>
                        </td>
                        <td>
                            <!-- un form por fila; los campos de arriba se unen a él con el atributo form -->
                            <form method="POST" id="<?= $f ?>" class="m-0">
                                <?= csrf_campo() ?>
                                <input type="hidden" name="id_usuario" value="<?= (int) $u["id_usuario"] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-primary" <?= $propio ? "disabled" : "" ?>>Guardar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
<?php pie(); ?>
