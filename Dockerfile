# ============================================================
# Dockerfile untuk Open Monograph Press (OMP)
# Repo: https://github.com/Amirul78800/omp
# Deploy: TrueNAS SCALE via Arcane (Docker Compose)
# ============================================================

FROM php:8.2-apache

# Install PHP extensions yang diperlukan OMP
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    libxml2-dev \
    libonig-dev \
    libxslt-dev \
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

# Clone repo OMP (dengan submodules)
RUN git clone --recurse-submodules https://github.com/Amirul78800/omp.git . \
    && git submodule update --init --recursive

# Install PHP dependencies via Composer
RUN composer install -d lib/pkp --no-dev --optimize-autoloader

# Buat direktori yang diperlukan OMP
RUN mkdir -p \
    public/files \
    cache/t_cache \
    cache/t_config \
    cache/t_compile \
    cache/_db \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 public/files cache

# Copy config template
RUN cp config.TEMPLATE.inc.php config.inc.php

# Setup Apache - enable mod_rewrite
RUN a2enmod rewrite

# Apache config untuk OMP
RUN echo '<Directory /var/www/html>\n\
    Options FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/omp.conf \
    && a2enconf omp

EXPOSE 80

CMD ["apache2-foreground"]
