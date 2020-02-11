FROM dandelionphp/php:7.4-cli-alpine

COPY --chown=dandelion:dandelion bin/dandelion.phar /usr/bin/dandelion

RUN set -eux; \
  chmod +x /usr/bin/dandelion; \
  dandelion --version