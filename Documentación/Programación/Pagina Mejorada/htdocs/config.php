<?php
// config.php - ajustes generales del proyecto

// Detecta si se instaló en la raíz del dominio o dentro de una carpeta.
$ruta_script = str_replace("\\", "/", $_SERVER["SCRIPT_FILENAME"] ?? "");
$ruta_proyecto = str_replace("\\", "/", __DIR__) . "/";
$url_script = $_SERVER["SCRIPT_NAME"] ?? "";
$ruta_relativa = strpos($ruta_script, $ruta_proyecto) === 0
    ? substr($ruta_script, strlen($ruta_proyecto))
    : "";
$sufijo = "/" . $ruta_relativa;
define("BASE_URL", $ruta_relativa !== "" && substr($url_script, -strlen($sufijo)) === $sufijo
    ? substr($url_script, 0, -strlen($sufijo))
    : "");

define("ROL_PACIENTE", 1);
// id del rol administrador en la tabla roles
define("ROL_ADMIN", 4);

// Qué visibilidades de documentos puede ver cada rol.
// AJUSTA los ids según tu tabla roles.
const VISIBILIDAD_POR_ROL = [
    ROL_PACIENTE => ["publico"],
    2 => ["publico", "medico"],
    3 => ["publico", "enfermero"],
    ROL_ADMIN => ["publico", "medico", "enfermero", "privado"],
];

const VISIBILIDADES_DOCUMENTOS = [
    "publico" => "Público",
    "medico" => "Médico",
    "enfermero" => "Enfermero",
    "privado" => "Privado para pacientes",
];

// Subida de archivos
define("CARPETA_SUBIDAS", __DIR__ . "/uploads/documentos/");
define("TAMANO_MAXIMO", 10 * 1024 * 1024); // 10 MB
const EXTENSIONES_PERMITIDAS = [
    "pdf"  => ["application/pdf"],
    "doc"  => ["application/msword", "application/octet-stream"],
    "docx" => ["application/vnd.openxmlformats-officedocument.wordprocessingml.document", "application/zip"],
    "jpg"  => ["image/jpeg"],
    "jpeg" => ["image/jpeg"],
    "png"  => ["image/png"],
];

// URL completa del sistema tal como la ve un celular (se usa dentro de los QR).
// Déjala vacía para usar la dirección con la que abriste el panel.
// Ej: "http://192.168.1.50/hospital"  (NO uses localhost: el celular no lo encuentra)

define("URL_PUBLICA", "");
