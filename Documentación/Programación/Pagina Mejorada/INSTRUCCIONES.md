# Hospital de Clínicas en InfinityFree

## Instalar o actualizar

1. Descomprimí el ZIP. En InfinityFree, creá o seleccioná la base MySQL desde el panel de control.
2. En phpMyAdmin, seleccioná esa base. Para una instalación nueva, importá `hospital_sistema.sql`. Para actualizar una instalación existente, exportá primero una copia de la base y luego importá solo `actualizar_documentos_pacientes.sql`. Si estás trasladando el sitio desde otro servidor, importá tu copia de la base anterior y ejecutá la actualización después.
3. En `htdocs/conexion.php`, completá el host MySQL, el usuario, la contraseña y el nombre exacto de la base que figuran en tu cuenta de InfinityFree.
4. Subí **el contenido** de la carpeta `htdocs` del ZIP a la carpeta `htdocs` del dominio. Incluí los archivos ocultos `.htaccess` de la raíz, `uploads` y `uploads/documentos`. Dejá los archivos SQL y estas instrucciones en tu computadora. Si actualizás un sitio existente, conservá sus archivos de `uploads/documentos` y reemplazá la aplicación por la nueva versión. Si hay un `index.html` de bienvenida del hosting, retiralo para que se abra `index.php`.
5. Abrí el dominio o `/login.php`. La aplicación detecta si está en la raíz o en una subcarpeta. Los QR nuevos toman el dominio desde el que entrás. Si necesitás fijar el dominio, completá `URL_PUBLICA` en `config.php`.

En una instalación nueva, creá tu cuenta desde **Crear cuenta**. Para habilitar el primer administrador, ejecutá en phpMyAdmin el siguiente comando con el correo exacto de esa cuenta. Volvé a iniciar sesión para entrar directo al panel de administración.

```sql
UPDATE usuarios SET id_rol = 4 WHERE email = 'tu-correo@ejemplo.com';
```

[Guía de migración de InfinityFree](https://forum.infinityfree.com/t/how-to-migrate-your-website-to-infinityfree/49315)

## Asignar documentos

1. Como administrador, entrá a **Asignar a pacientes** desde el panel de administración.
2. Para subir un archivo personal, usá **Agregar documento privado** y guardalo como **Privado para pacientes**. Volvé a **Asignar a pacientes**, elegí el documento activo y la cuenta activa, y presioná **Asignar documento**. El rol **Usuario** del sistema corresponde a las cuentas de pacientes.
3. El paciente entra a **Mis documentos** desde su cuenta y abre el archivo. En **Asignaciones actuales**, el administrador puede quitar el acceso de una cuenta.
4. También podés asignar documentos públicos. En ese caso, el documento aparece en **Mis documentos** de la cuenta elegida y sigue disponible en el portal público. Los documentos privados mantienen esa visibilidad al editarlos.

Después de subir el sitio, probá con un documento ficticio. Confirmá que la cuenta asignada puede abrirlo y que una visita directa a la ruta del archivo dentro de `/uploads/documentos/` devuelve un error de acceso.
