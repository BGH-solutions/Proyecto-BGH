<?php
// config.php - ajustes generales del proyecto

// Ruta del proyecto vista desde el navegador, sin barra final.
// Ej: si entras por http://localhost/hospital/loginn.php => "/hospital"
// Si lo tienes directo en htdocs (http://localhost/loginn.php) => ""
define("BASE_URL", "/hospital");

// id del rol administrador en la tabla roles
define("ROL_ADMIN", 4);

// Qué visibilidades de documentos puede ver cada rol.
// AJUSTA los ids según tu tabla roles.
const VISIBILIDAD_POR_ROL = [
    1 => ["publico"],
    2 => ["publico", "medico"],
    3 => ["publico", "enfermero"],
    4 => ["publico", "medico", "enfermero"],
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

define("URL_PUBLICA", "http://192.168.1.12/hospital");