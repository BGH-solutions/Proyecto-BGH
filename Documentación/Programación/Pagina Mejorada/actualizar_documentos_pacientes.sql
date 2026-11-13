-- Para instalaciones existentes. Seleccioná la base del hospital antes de importar.
ALTER TABLE documentos
  MODIFY COLUMN visibilidad ENUM('publico','medico','enfermero','privado') NOT NULL DEFAULT 'publico';

CREATE TABLE IF NOT EXISTS documentos_pacientes (
  id_paciente      INT UNSIGNED NOT NULL,
  id_documento     INT UNSIGNED NOT NULL,
  fecha_asignacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_paciente, id_documento),
  KEY idx_documento_paciente (id_documento),
  FOREIGN KEY (id_paciente) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
  FOREIGN KEY (id_documento) REFERENCES documentos(id_documento) ON DELETE CASCADE
) ENGINE=InnoDB;
