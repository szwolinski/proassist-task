DOCKER_COMPOSE = docker-compose -f docker-compose.yaml
PHP_CONTAINER  = $(DOCKER_COMPOSE) exec -T app
CONSOLE        = $(PHP_CONTAINER) php bin/console

.DEFAULT_GOAL := help

.PHONY: help
help: ## Display this help message
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

# --- DEV ---
.PHONY: build
build: ## Build Docker images
	$(DOCKER_COMPOSE) build

.PHONY: up
up: ## Start Docker containers in background
	$(DOCKER_COMPOSE) up -d

.PHONY: down
down: ## Stop containers and remove orphans
	$(DOCKER_COMPOSE) down --remove-orphans

.PHONY: restart
restart: ## Restart development environment
	$(MAKE) down
	$(MAKE) up

.PHONY: logs
logs: ## Follow Docker logs in real time
	$(DOCKER_COMPOSE) logs -f

# --- DB ---
.PHONY: db-migrate
db-migrate: ## Run database migrations
	$(CONSOLE) doctrine:migrations:migrate --no-interaction --allow-no-migration

.PHONY: db-diff
db-diff: ## Generate a new database migration compare to mapping
	$(CONSOLE) doctrine:migrations:diff --no-interaction

.PHONY: db-fixtures
db-fixtures: ## Load database fixtures
	$(CONSOLE) doctrine:fixtures:load --no-interaction

.PHONY: db-reset
db-reset: ## Drop, recreate database and reload schema with fixtures
	$(CONSOLE) doctrine:database:drop --force --if-exists
	$(CONSOLE) doctrine:database:create --if-not-exists
	$(CONSOLE) doctrine:migrations:migrate --no-interaction
	$(CONSOLE) doctrine:fixtures:load --no-interaction

# --- Tests & Static Analysis ---
.PHONY: cc
cc: ## Clear Symfony cache
	$(CONSOLE) cache:clear

.PHONY: test
test: ## Run PHPUnit tests and stop on first failure
	$(PHP_CONTAINER) env SYMFONY_DEPRECATIONS_HELPER=disabled=1 vendor/bin/phpunit --stop-on-failure

.PHONY: phpstan
phpstan: ## Run PHPStan static analysis at level 8
	$(PHP_CONTAINER) vendor/bin/phpstan analyse --memory-limit=1G

# --- Prod ---
.PHONY: prod-build
prod-build: ## Build production Docker images without cache
	docker-compose -f docker-compose.prod.yaml build --no-cache

# --- Init ---
.PHONY: init
init: ## Initialize development environment (build, up, migrate)
	$(MAKE) build
	$(MAKE) up
	$(MAKE) db-migrate
