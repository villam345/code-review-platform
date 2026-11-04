FROM php:8.2-cli

RUN docker-php-ext-install pdo_mysql

WORKDIR /app
COPY . /app

RUN mkdir -p /app/storage/uploads \
    && chmod -R 0777 /app/storage

EXPOSE 8080
CMD ["php", "-S", "0.0.0.0:8080", "-t", "/app"]
