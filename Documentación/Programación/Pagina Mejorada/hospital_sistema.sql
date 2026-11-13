-- Base de datos del sistema del Hospital de Clínicas


-- CREATE DATABASE IF NOT EXISTS hospital_sistema
  -- CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE hospital_sistema;

-- ---------- Roles ----------
CREATE TABLE IF NOT EXISTS roles (
  id_rol     INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nombre_rol VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

INSERT INTO roles (id_rol, nombre_rol) VALUES
  (1, 'Usuario'),
  (2, 'Médico'),
  (3, 'Enfermero'),
  (4, 'Administrador')
ON DUPLICATE KEY UPDATE nombre_rol = VALUES(nombre_rol);

-- ---------- Usuarios ----------
CREATE TABLE IF NOT EXISTS usuarios (
  id_usuario INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  id_rol     INT UNSIGNED NOT NULL DEFAULT 1,
  nombre     VARCHAR(80)  NOT NULL,
  apellido   VARCHAR(80)  NOT NULL,
  email      VARCHAR(150) NOT NULL UNIQUE,
  contrasena VARCHAR(255) NOT NULL,
  estado     TINYINT(1)   NOT NULL DEFAULT 1,
  FOREIGN KEY (id_rol) REFERENCES roles(id_rol)
) ENGINE=InnoDB;

-- ---------- Documentos ----------
CREATE TABLE IF NOT EXISTS categorias_documentos (
  id_categoria     INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nombre_categoria VARCHAR(150) NOT NULL
) ENGINE=InnoDB;

INSERT INTO categorias_documentos (nombre_categoria) VALUES
  ('Indicaciones de interrupción voluntaria del embarazo.'),
  ('Prostatectomía radical (indicaciones e información para el paciente).'),
  ('Preparación para estudios imagenológicos.'),
  ('Estudios diagnósticos con pertecnetato.'),
  ('Centellograma de perfusión miocárdica.'),
  ('Indicaciones ecocardiograma con dobutamina.'),
  ('Indicaciones para pacientes en tratamiento con warfarina.'),
  ('Indicaciones ecocardiograma transesofágico.'),
  ('Indicaciones para ingreso a centro de nefrología y trasplante.'),
  ('Plan de alta enfermería, Nefrología.'),
  ('Indicaciones de enfermería para usuarios trasplantados.'),
  ('Prevención de infecciones.');

CREATE TABLE IF NOT EXISTS documentos (
  id_documento INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  id_categoria INT UNSIGNED NOT NULL,
  id_usuario   INT UNSIGNED NOT NULL,
  titulo       VARCHAR(100) NOT NULL,
  descripcion  TEXT NULL,
  archivo      VARCHAR(200) NOT NULL,
  fecha_subida DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  estado       TINYINT(1) NOT NULL DEFAULT 1,
  visibilidad  ENUM('publico','medico','enfermero','privado') NOT NULL DEFAULT 'publico',
  FOREIGN KEY (id_categoria) REFERENCES categorias_documentos(id_categoria),
  FOREIGN KEY (id_usuario)   REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB;

-- ---------- Documentos asignados ----------
CREATE TABLE IF NOT EXISTS documentos_pacientes (
  id_paciente      INT UNSIGNED NOT NULL,
  id_documento     INT UNSIGNED NOT NULL,
  fecha_asignacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_paciente, id_documento),
  KEY idx_documento_paciente (id_documento),
  FOREIGN KEY (id_paciente) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
  FOREIGN KEY (id_documento) REFERENCES documentos(id_documento) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- Encuestas ----------
CREATE TABLE IF NOT EXISTS encuestas (
  id_encuesta  INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  titulo       VARCHAR(150) NOT NULL,
  descripcion  TEXT NULL,
  estado       TINYINT(1) NOT NULL DEFAULT 1,
  fecha_inicio DATE NOT NULL,
  fecha_fin    DATE NULL
) ENGINE=InnoDB;

INSERT INTO encuestas (titulo, descripcion, estado, fecha_inicio, fecha_fin) VALUES
  ('Encuesta de satisfacción 1', 'Contanos cómo fue tu experiencia en el hospital.', 1, '2024-01-01', NULL),
  ('Encuesta de satisfacción 2', NULL, 1, '2026-01-01', NULL),
  ('Encuesta de satisfacción 3', NULL, 1, '2026-01-01', NULL),
  ('Encuesta de satisfacción 4', NULL, 1, '2026-01-01', NULL);

CREATE TABLE IF NOT EXISTS servicios (
  id_servicio     INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nombre_servicio VARCHAR(100) NOT NULL,
  estado          TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

INSERT INTO servicios (nombre_servicio) VALUES
  ('Otro'),
  ('Nefrología'),
  ('Cardiología'),
  ('Imagenología'),
  ('Medicina nuclear'),
  ('Urología'),
  ('Ginecología');

CREATE TABLE IF NOT EXISTS respuestas_encuestas (
  id_respuesta         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  id_encuesta          INT UNSIGNED NOT NULL,
  id_servicio          INT UNSIGNED NOT NULL,
  atencion_personal    TINYINT UNSIGNED NOT NULL,
  tiempo_de_espera     TINYINT UNSIGNED NOT NULL,
  limpieza             TINYINT UNSIGNED NOT NULL,
  satisfaccion_general TINYINT UNSIGNED NOT NULL,
  comentario           VARCHAR(200) NULL,
  fecha_respuesta      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_encuesta) REFERENCES encuestas(id_encuesta),
  FOREIGN KEY (id_servicio) REFERENCES servicios(id_servicio)
) ENGINE=InnoDB;
