FROM mysql/mysql-server:8.0

ENV TZ=UTC

COPY ./docker/db/my.cnf /etc/my.cnf
COPY ./docker/db/init-sql /docker-entrypoint-initdb.d
