.PHONY: phpcs phpstan codeception test install install-dev bundle docker-tag docker-login docker-push docker-build

install:
	composer install --no-dev

install-dev:
	composer install

bundle: install
	box compile

docker-tag: docker-build
	docker tag dandelion dandelionphp/dandelion:$(TRAVIS_TAG)

docker-build:
	docker build -t dandelion .

docker-login:
	echo "$(DOCKER_PASSWORD)" | docker login -u "$(DOCKER_USERNAME)" --password-stdin

docker-push: docker-login
	docker push dandelionphp/dandelion:latest
	docker push dandelionphp/dandelion:$(TRAVIS_TAG)

phpcs:
	./vendor/bin/phpcs --standard=./vendor/squizlabs/php_codesniffer/src/Standards/PSR12/ruleset.xml ./src/

phpstan:
	./vendor/bin/phpstan analyse -l 7 ./src

codeception:
	./vendor/bin/codecept run

phpmd:
	./vendor/bin/phpmd ./src xml cleancode,codesize,controversial,design --exclude DandelionServiceProvider

phpcpd:
	./vendor/bin/phpcpd ./src

test: install-dev phpcs phpstan codeception phpmd phpcpd
