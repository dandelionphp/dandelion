.PHONY: phpcs phpstan codeception test install

install:
	composer install

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

test: phpcs phpstan codeception phpmd phpcpd
	@echo "Run CI suite"
