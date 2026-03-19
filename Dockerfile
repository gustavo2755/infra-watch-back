FROM php:8.3-cli

RUN docker-php-ext-install pdo_mysql

# pdo_sqlite is included in the base image and used for tests (SQLite in memory)

WORKDIR /var/www/html

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
