#!/bin/sh

isCommand() {
  if [ "$1" = "sh" ]; then
    return 1
  fi

  dandelion help "$1" > /dev/null 2>&1
}

if [ "${1#-}" != "$1" ]; then
  set -- dandelion "$@"
elif [ "$1" = 'dandelion' ]; then
  set -- "$@"
elif isCommand "$1"; then
  set -- dandelion "$@"
fi

exec "$@"
