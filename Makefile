.PHONY: up down logs sh test coverage coverage-html

up:
	docker compose up -d

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
