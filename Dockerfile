# ============================================================
# Dockerfile untuk Open Monograph Press (OMP)
# CARA GUNA: GitHub Actions akan COPY kod ke dalam image
# JANGAN clone dalam Dockerfile — guna COPY sahaja
# ============================================================

FROM php:8.2-apache

# Install dependencies
RUN apt-get update && apt-get install -y \
    unzip \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    libxml2-dev \
    libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        gd \
        pdo \
        pdo_mysql \
        mysqli \
        zip \
        intl \
        xml \
        mbstring \
        bcmath \
        opcache \
        ftp \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# COPY kod dari GitHub Actions (bukan clone)
COPY . .

# Install PHP dependencies
RUN composer install -d lib/pkp --no-dev --optimize-autoloader --no-interaction

# Buat direktori yang diperlukan
RUN mkdir -p \
    cache/t_cache \
    cache/t_config \
    cache/t_compile \
    cache/_db \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 cache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Apache config
RUN printf '<Directory /var/www/html>\n\
    Options FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>\n' > /etc/apache2/conf-available/omp.conf \
    && a2enconf omp

EXPOSE 80
CMD ["apache2-foreground"]
