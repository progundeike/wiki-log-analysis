FROM php:8.1

# COPY php.ini
COPY ./docker/php/php.ini /usr/local/etc/php/php.ini

# Composer install
COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer

RUN apt-get update && \
  # --no-install-recommendで不要なパッケージのインストールを防止
  apt-get -y install --no-install-recommends git unzip libzip-dev libicu-dev libonig-dev && \
  # 以下2行でパッケージインストールで使用したキャッシュファイルを削除
  apt-get clean && \
  rm -rf /var/lib/apt/lists/* && \
  # PHPの拡張ライブラリをインストール
  docker-php-ext-install intl pdo_mysql zip bcmath

WORKDIR /app
