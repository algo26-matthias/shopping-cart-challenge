.PHONY: up down logs sh test coverage coverage-html stan

ENV_FILE=.env
ENV_EXAMPLE=.env.example

up: ensure-env
	docker compose up -d
	docker compose exec php composer install --no-interaction --prefer-dist

down:
	docker compose down

logs:
	docker compose logs -f

sh:
	docker compose exec php sh

test:
	docker compose exec -e APP_ENV=test -e APP_DEBUG=1 php php bin/phpunit

coverage:
	docker compose exec -e APP_ENV=test php php -d pcov.enabled=1 bin/phpunit --coverage-text

coverage-html:
	docker compose exec -e APP_ENV=test php php -d pcov.enabled=1 bin/phpunit --coverage-html var/coverage

stan:
	docker compose exec php vendor/bin/phpstan analyse src

ensure-env:
	@if [ ! -f $(ENV_FILE) ]; then \
		echo "No .env found. Creating from .env.example"; \
		cp $(ENV_EXAMPLE) $(ENV_FILE); \
	fi
