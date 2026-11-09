<?php
// includes/funciones.php - helpers y plantilla común (encabezado + pie)

require_once __DIR__ . "/../config.php";

// escapa texto antes de mostrarlo en HTML
function e($texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}

function es_admin(): bool
{
    return isset($_SESSION["id_rol"]) && (int) $_SESSION["id_rol"] === ROL_ADMIN;
}

function exigir_admin(): void
{
    if (!es_admin()) {
        http_response_code(403);
        exit("No tenés permisos para realizar esta acción.");
    }
}

// ---------- CSRF (para formularios POST de administración) ----------
function csrf_token(): string
{
    if (empty($_SESSION["csrf"])) {
        $_SESSION["csrf"] = bin2hex(random_bytes(32));
    }
    return $_SESSION["csrf"];
}

function csrf_campo(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_validar(): void
{
    $enviado = $_POST["csrf"] ?? "";
    if (!is_string($enviado) || !hash_equals($_SESSION["csrf"] ?? "", $enviado)) {
        http_response_code(403);
        exit("Solicitud no válida. Volvé atrás y recargá la página.");
    }
}

// ---------- Plantilla ----------
// $activo: "documentos" | "encuesta" | ""   $publico: true = sin menú ni usuario (login, encuesta anónima)
function encabezado(string $titulo, string $activo = "", bool $publico = false): void
{
    $base = BASE_URL;
    $logueado = isset($_SESSION["id_usuario"]);
    $urlDocumentos = $base . "/admin/documentos/documentoss.php";
    $urlEncuesta = es_admin()
        ? $base . "/admin/encuestas/encuestas.php"
        : $base . "/encuestas/encuestaa.php";
    ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titulo) ?> - Hospital de Clínicas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= $base ?>/tb.css">
</head>
<body>

<?php if ($publico || !$logueado): ?>
    <header class="header header-login">
        <img src="<?= $base ?>/logo.png" alt="Hospital de Clínicas" class="logo">
    </header>
<?php else: ?>
    <header class="header">
        <a href="<?= $base ?>/pannel.php"><img src="<?= $base ?>/logo.png" alt="Hospital de Clínicas" class="logo"></a>

        <div class="usuario">
            <span><?= e($_SESSION["nombre_rol"] ?? "") ?></span>
            <span><?= e(($_SESSION["nombre"] ?? "") . " " . ($_SESSION["apellido"] ?? "")) ?></span>
            <div class="dropdown">
                <a href="#" class="text-dark fs-3" data-bs-toggle="dropdown" aria-label="Menú de usuario">
                    <i class="bi bi-list"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="<?= $base ?>/pannel.php">Inicio</a></li>
                    <?php if (es_admin()): ?>
                        <li><a class="dropdown-item" href="<?= $base ?>/admin/panel.adminn.php">Administración</a></li>
                    <?php endif; ?>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="<?= $base ?>/cerrar_sesionn.php">Cerrar sesión</a></li>
                </ul>
            </div>
        </div>
    </header>

    <nav class="menu">
        <ul>
            <li class="<?= $activo === "documentos" ? "active" : "" ?>"><a href="<?= $urlDocumentos ?>"> Documentos </a></li>
            <li class="<?= $activo === "encuesta" ? "active" : "" ?>"><a href="<?= $urlEncuesta ?>"> Encuesta</a></li>
        </ul>
    </nav>
<?php endif; ?>
<?php
}

// $scripts: HTML extra (por ejemplo, Chart.js) que va antes de cerrar el body
function pie(string $scripts = ""): void
{
    ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?= $scripts ?>
</body>
</html>
<?php
}

// ---------- Archivos subidos ----------
// Devuelve [nombre_guardado, error]. Si no se subió nada y no es obligatorio: ["", null]
function procesar_subida(string $campo, bool $obligatorio): array
{
    $f = $_FILES[$campo] ?? null;

    if (!$f || $f["error"] === UPLOAD_ERR_NO_FILE) {
        return $obligatorio ? ["", "Seleccioná un archivo."] : ["", null];
    }
    if ($f["error"] !== UPLOAD_ERR_OK) {
        return ["", "No se pudo subir el archivo (código " . (int) $f["error"] . ")."];
    }
    if ($f["size"] > TAMANO_MAXIMO) {
        return ["", "El archivo supera los 10 MB."];
    }

    $ext = strtolower(pathinfo($f["name"], PATHINFO_EXTENSION));
    if (!isset(EXTENSIONES_PERMITIDAS[$ext])) {
        return ["", "Tipo de archivo no permitido (usá PDF, Word, JPG o PNG)."];
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f["tmp_name"]);
    if (!in_array($mime, EXTENSIONES_PERMITIDAS[$ext], true)) {
        return ["", "El contenido del archivo no coincide con su extensión."];
    }

    if (!is_dir(CARPETA_SUBIDAS)) {
        mkdir(CARPETA_SUBIDAS, 0755, true);
    }

    $guardado = bin2hex(random_bytes(16)) . "." . $ext;
    if (!move_uploaded_file($f["tmp_name"], CARPETA_SUBIDAS . $guardado)) {
        return ["", "No se pudo guardar el archivo en el servidor."];
    }
    return [$guardado, null];
}

// ---------- URL absoluta (para los QR) ----------
function url_publica(string $ruta): string
{
    if (URL_PUBLICA !== "") {
        $raiz = rtrim(URL_PUBLICA, "/");
    } else {
        $https = !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off";
        $raiz = ($https ? "https" : "http") . "://" . ($_SERVER["HTTP_HOST"] ?? "localhost") . BASE_URL;
    }
    return $raiz . "/" . ltrim($ruta, "/");
}

// Devuelve el HTML de un código QR (se dibuja en el navegador con qrcodejs)
// con botones para descargarlo en PNG o imprimirlo. Usar una sola vez por página.
function bloque_qr(string $url, string $nombre): string
{
    $u = e($url);
    $js_url = json_encode($url, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    $archivo = preg_replace('/[^A-Za-z0-9_-]+/', "_", $nombre);
    $archivo = trim((string) $archivo, "_") ?: "qr";

    return <<<HTML
<div class="text-center">
    <div id="qr" class="d-inline-block bg-white p-3 border"></div>
    <p class="mt-3 mb-1 small text-muted">El QR lleva a:</p>
    <p class="small text-break"><a href="$u" target="_blank" rel="noopener">$u</a></p>
    <div class="d-flex justify-content-center gap-2 d-print-none">
        <button type="button" class="btn btn-primary" id="btnDescargarQr">Descargar PNG</button>
        <button type="button" class="btn btn-secondary" onclick="window.print()">Imprimir</button>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
(function () {
    var cont = document.getElementById("qr");
    new QRCode(cont, { text: $js_url, width: 256, height: 256, correctLevel: QRCode.CorrectLevel.M });
    document.getElementById("btnDescargarQr").addEventListener("click", function () {
        var canvas = cont.querySelector("canvas");
        var a = document.createElement("a");
        a.href = canvas.toDataURL("image/png");
        a.download = "$archivo.png";
        a.click();
    });
})();
</script>
HTML;
}

// ---------- Plantilla liviana para celulares (pacientes, sin login) ----------
function encabezado_movil(string $titulo): void
{
    $base = BASE_URL;
    ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titulo) ?> - Hospital de Clínicas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f2f5f7; }
        .barra { background: #fff; border-bottom: 4px solid #65bde5; }
        .btn-hc { background: #65bde5; color: #111; border: 0; font-weight: 600; }
        .btn-hc:hover, .btn-hc:focus { background: #4eafd9; color: #111; }
    </style>
</head>
<body>
<header class="barra py-2 px-3 d-flex align-items-center justify-content-between">
    <a href="<?= $base ?>/publico/index.php"><img src="<?= $base ?>/logo.png" alt="Hospital de Clínicas" style="height:48px"></a>
    <a href="<?= $base ?>/loginn.php" class="small text-decoration-none">Personal del hospital</a>
</header>
<?php
}

function pie_movil(string $scripts = ""): void
{
    echo $scripts;
    echo "\n</body>\n</html>\n";
}
