FROM php:8.3-fpm

# Perbarui dan instal dependensi yang diperlukan
RUN apt-get update && apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql

# Set lokasi kerja dalam container
WORKDIR /var/www/html

# Salin file dari direktori lokal ke dalam container
COPY . /var/www/html

# Salin file php.ini jika diperlukan
COPY php.ini /usr/local/etc/php/php.ini

# Perbaiki hak akses
RUN chown -R www-data:www-data /var/www/html

# Perintah utama untuk menjalankan PHP-FPM
CMD ["php-fpm"]
