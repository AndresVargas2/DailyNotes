--- USER
CREATE TABLE usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_completo VARCHAR(50) NOT NULL,
    cedula int(128) UNIQUE NOT NULL,
    correo VARCHAR(128) NOT NULL,
    contra VARCHAR(128) NOT NULL,
    rol ENUM('admin', 'empleado') NOT NULL,
    estado tinyint(1) DEFAULT 1,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE tareas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(100) NOT NULL,
    descripcion TEXT,
    asignado_a INT,
    estado ENUM('pendiente', 'en_progreso', 'completado') DEFAULT 'pendiente',
    prioridad ENUM('baja', 'media', 'alta') DEFAULT 'media',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_asignacion DATETIME,
    activo tinyint DEFAULT 1,
    FOREIGN KEY (asignado_a) REFERENCES usuario(id)
);

CREATE TABLE notas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tarea_id INT NOT NULL,
    contenido TEXT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tarea_id) REFERENCES tareas(id)
);

-- Recordatorios asociados a tareas
CREATE TABLE recordatorios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tarea_id INT NOT NULL,
    fecha_hora DATETIME NOT NULL,
    FOREIGN KEY (tarea_id) REFERENCES tareas(id)
);

-- Etiquetas reutilizables para tareas
CREATE TABLE etiquetas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL
);

-- Relación muchas-a-muchas entre tareas y etiquetas
CREATE TABLE tarea_etiqueta (
    tarea_id INT NOT NULL,
    etiqueta_id INT NOT NULL,
    PRIMARY KEY (tarea_id, etiqueta_id),
    FOREIGN KEY (tarea_id) REFERENCES tareas(id),
    FOREIGN KEY (etiqueta_id) REFERENCES etiquetas(id)
);

-- Notificaciones para usuarios
CREATE TABLE notificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mensaje TEXT NOT NULL,
    beneficiario INT NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fue_leido BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (beneficiario) REFERENCES usuario(id)
);
