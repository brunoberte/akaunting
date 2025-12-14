FROM php:7.4-fpm

# Copy composer.lock and composer.json
COPY composer.lock composer.json /var/www/

# Set working directory
WORKDIR /var/www

# Install dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    locales \
    zip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libmcrypt-dev \
    libgd-dev \
    jpegoptim optipng pngquant gifsicle \
    libonig-dev \
    libxml2-dev \
    vim \
    unzip \
    curl \
    libzip-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd mysqli

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Crie um usuário e grupo não-root
RUN addgroup --gid 1000 appgroup && adduser --uid 1000 --gid 1000 --shell /bin/sh --disabled-login appuser

# Defina as permissões no diretório do código
RUN chown -R appuser:appgroup /var/www

VOLUME /var/www

# Copy existing application directory contents
#COPY . /var/www

# Copy existing application directory permissions
#COPY --chown=www:www . /var/www

# Change current user to www
#USER www

#RUN composer install

RUN echo "user = appuser" >> /usr/local/etc/php-fpm.d/www.conf
RUN echo "group = appuser" >> /usr/local/etc/php-fpm.d/www.conf

# Mude para o usuário não-root para todos os comandos subsequentes
USER appuser

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm", "-R"]
