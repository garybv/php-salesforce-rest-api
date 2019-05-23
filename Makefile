UID_GID := $(shell id -u):$(shell id -g)
DOCKER_RUN := docker run --rm --interactive --tty \
	--volume $(shell pwd):/app \
	--volume $(shell pwd)/tmp:/tmp \
	--user $(shell id -u):$(shell id -g) \
	php-salesforce-rest-api-composer

build:
	docker build . -t php-salesforce-rest-api-composer

bash:
	$(DOCKER_RUN) bash

composer-install:
	mkdir -p tmp && $(DOCKER_RUN) composer install

cleanup:
	rm -rf ./tmp ./vendor ./composer.lock
