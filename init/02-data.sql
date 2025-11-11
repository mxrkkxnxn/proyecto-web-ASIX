-- ~/proyectos/webapp/init/02-data.sql
INSERT IGNORE INTO usuarios (nombre, email, password, es_admin) VALUES
    ('Cliente', 'cliente@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0),
    ('Admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

INSERT IGNORE INTO servicios (nombre, descripcion, precio) VALUES
    ('Diseño Web', 'Página web responsive con CMS', 499.99),
    ('SEO Básico', 'Optimización para motores de búsqueda', 199.50),
    ('Mantenimiento Mensual', 'Soporte técnico y actualizaciones', 79.90);
