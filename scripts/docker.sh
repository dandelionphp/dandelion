#!/bin/bash
echo "$DOCKER_PASSWORD" | docker login -u "$DOCKER_USERNAME" --password-stdin
docker push dandelionphp/dandelion:latest
docker push dandelionphp/dandelion:$TRAVIS_TAG