FROM --platform=linux/amd64 php:8.2-cli

RUN docker-php-ext-install mysqli pdo pdo_mysql

WORKDIR /app
COPY index.php .

EXPOSE 80

CMD ["php", "-S", "0.0.0.0:80", "index.php"]    