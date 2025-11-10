FROM php:8.3-apache

# --- INICIO DE NUEVO BLOQUE ---
# Instala las dependencias del sistema para la extensión 'gd' (imágenes)
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    && rm -rf /var/lib/apt/lists/*

# Configura e instala 'gd' Y las extensiones que ya tenías
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd mysqli pdo pdo_mysql
# --- FIN DE NUEVO BLOQUE ---

# Módulos de Apache (esto se queda igual)
RUN a2enmod rewrite
RUN a2enmod ssl

# Configuración de SSL (esto se queda igual)
COPY ssl.conf /etc/apache2/sites-available/000-default-ssl.conf
RUN a2ensite 000-default-ssl.conf

WORKDIR /var/www/html
