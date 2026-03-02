.PHONY: up down ps logs sh test lint

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
	echo "not yet implemented"

lint:
	echo "not yet implemented"

composer:
	docker compose run --rm php composer $(ARGS)
