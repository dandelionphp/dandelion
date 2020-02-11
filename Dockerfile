FROM dandelionphp/php:7.4-cli-alpine

ADD bin/dandelion.phar /usr/bin/dandelion

RUN set -eux; \
  chmod +x /usr/bin/dandelion; \
  dandelion --version