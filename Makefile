.PHONY: up down ps logs sh test lint composer

up:
	docker compose up -d

down:
	docker compose down

ps:
	docker compose ps

logs:
	docker compose logs -f

sh:
	docker compose exec php sh

test:
	docker compose exec -e APP_ENV=test -e APP_DEBUG=1 php php bin/phpunit

lint:
	echo "not yet implemented"

composer:
	docker compose run --rm php composer $(ARGS)
