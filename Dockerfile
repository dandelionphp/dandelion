FROM golang:1.15-alpine AS compiler

RUN apk add make cmake pkgconfig musl-dev gcc git

RUN go get -d github.com/libgit2/git2go; \
    cd $GOPATH/src/github.com/libgit2/git2go; \
    git checkout next; \
    git submodule update --init; \
    make install;

RUN go get github.com/splitsh/lite; \
    go build github.com/splitsh/lite

FROM dandelionphp/php:7.4-cli-alpine

COPY --from=compiler /go/bin/lite /usr/local/bin/splitsh-lite
COPY --chown=dandelion:dandelion bin/dandelion.phar /usr/bin/dandelion

RUN set -eux; \
  chmod +x /usr/bin/dandelion; \
  dandelion --version --no-interaction --ansi

USER root

COPY ./docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh

USER dandelion

ENTRYPOINT ["docker-entrypoint.sh"]

CMD ["dandelion"]
