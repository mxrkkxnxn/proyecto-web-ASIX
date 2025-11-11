-- init/03-login-attempts.sql
CREATE TABLE IF NOT EXISTS intentos_login (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL,
    intentos INT DEFAULT 1,
    ultimo_intento TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    bloqueado_hasta TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_bloqueado (bloqueado_hasta)
);
