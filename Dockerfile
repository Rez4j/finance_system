FROM php:8.2-apache

# Install dependencies and PDO MySQL
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql

# 1. Enable mod_rewrite for pretty URLs and .htaccess
RUN a2enmod rewrite

# 2. CRITICAL: Allow .htaccess to override Apache defaults
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

WORKDIR /var/www/html
COPY . /var/www/html/

# Handle Render's dynamic PORT
RUN sed -i 's/Listen 80/Listen ${PORT}/g' /etc/apache2/ports.conf
RUN sed -i 's/<VirtualHost \*:80>/<VirtualHost \*:${PORT}>/g' /etc/apache2/sites-available/000-default.conf

RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

CMD ["apache2-foreground"]
