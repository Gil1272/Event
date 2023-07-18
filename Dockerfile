FROM php:8.2-fpm

RUN mkdir -p /var/www/html/eventapi
RUN chmod -R 777 /var/www/html/eventapi

# Create the cache directory
RUN mkdir -p /var/www/.composer/cache/files/


# Install system dependencies
RUN apt-get update && apt-get install -y \
		libfreetype-dev \
		libjpeg62-turbo-dev \
		libpng-dev \
		git \
		curl \
		libpng-dev \
		libonig-dev \
		libxml2-dev \
		zip \
		unzip

# Install PHP extensions
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions gd xdebug mongodb @composer mbstring exif pcntl bcmath

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . /var/www/html/eventapi

# Add UID '1000' to www-data
RUN usermod -u 1000 www-data

# Copy existing application directory permissions
COPY --chown=www-data:www-data . /var/www/html/eventapi

# Set proper permissions
RUN chown -R www-data:www-data /var/www/.composer

# Change current user to www
USER www-data

# RUN echo "memory_limit = 256M" > /usr/local/etc/php/conf.d/memory-limit.ini
COPY ./docker-compose/php/laravel.ini /usr/local/etc/php/conf.d/laravel.ini

WORKDIR /var/www/html/eventapi

# Get the IP address of the host machine && # Replace the placeholder in the Nginx configuration template
RUN HOST_IP=$(hostname -I | awk '{print $1}') && sed -i "s/\$HOST_IP/$HOST_IP/g" ./docker-compose/nginx/default.conf

# Expose port 9000 and start php-fpm server
EXPOSE 9000

CMD ["php-fpm"]

