# Dockerfile for SPD Sports Therapy
# Extends official PHP 8.1 Apache image with required extensions

FROM php:8.1-apache

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    mysql-client \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions required by the application
RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache mod_rewrite for URL rewriting (if needed in future)
RUN a2enmod rewrite

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html

# Copy PHP configuration (if needed)
# COPY php.ini /usr/local/etc/php/

# Expose port 80 (Apache will listen on this)
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
