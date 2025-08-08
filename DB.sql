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
    FOREIGN KEY (asignado_a) REFERENCES usuario(id)
);

CREATE TABLE notificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mensaje TEXT NOT NULL,
    benificiario INT NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    fecha DATE NOT NULL,
    fue_leido BOOLEAN DEFAULT FALSE
);
