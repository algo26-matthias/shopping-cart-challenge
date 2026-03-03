FROM php:8.5-fpm

WORKDIR /app

# System deps for common PHP extensions and tooling
RUN apt-get update && apt-get install -y --no-install-recommends \
    $PHPIZE_DEPS \
    git \
    unzip \
    zip \
    libicu-dev \
    libzip-dev \
    default-libmysqlclient-dev \
  && docker-php-ext-install -j"$(nproc)" \
    pdo_mysql \
    intl \
    zip \
  && rm -rf /var/lib/apt/lists/*

# APCu for caching in dev (not mandatory)
RUN pecl install apcu pcov \
  && docker-php-ext-enable apcu pcov

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Custom php.ini
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-app.ini

# Create non-root user for volume permissions
RUN useradd -m -u 1000 appuser
USER appuser
