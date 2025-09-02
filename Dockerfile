FROM php:8.3-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libxml2-dev \
    libonig-dev \
    libzip-dev \
    && rm -rf /var/lib/apt/lists/*

# Install Xdebug for code coverage
RUN pecl install xdebug && docker-php-ext-enable xdebug
RUN echo "xdebug.mode=coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Install PHP extensions
RUN docker-php-ext-install \
    dom \
    mbstring \
    simplexml \
    xml \
    iconv \
    zip

# Install Composer (specific version to avoid deprecation notices)
COPY --from=composer:2.8.11 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy composer files first for better caching
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-scripts --no-autoloader --prefer-dist

# Copy application code
COPY . .

# Generate autoloader
RUN composer dump-autoload --optimize

# Set proper permissions
RUN chown -R www-data:www-data /app

# Create user for running tests (non-root)
RUN useradd -m -s /bin/bash testuser && \
    chown -R testuser:testuser /app

USER testuser

# Default command
CMD ["./vendor/bin/phpunit"]
