# =============================================================================
# Pizza Shop API — Makefile
# =============================================================================

SHELL := /bin/bash
.DEFAULT_GOAL := help

# -----------------------------------------------------------------------------
# Config
# -----------------------------------------------------------------------------
COMPOSE      := docker compose
COMPOSE_PROD := docker compose -f docker-compose.yml -f docker-compose.prod.yaml
PHP          := $(COMPOSE) exec php
PHP_RUN      := $(COMPOSE) run --rm php
CONSOLE      := $(PHP) php bin/console
COMPOSER     := $(PHP) composer
NGINX_PORT   ?= 8080

# -----------------------------------------------------------------------------
# Colors
# -----------------------------------------------------------------------------
GREEN  := \033[0;32m
YELLOW := \033[0;33m
BLUE   := \033[0;34m
RESET  := \033[0m

##@ General

.PHONY: help
help: ## Show available commands
	@awk 'BEGIN {FS = ":.*##"; printf "\nUsage:\n  make \033[36m<target>\033[0m\n"} /^[a-zA-Z_0-9-]+:.*?##/ { printf "  \033[36m%-18s\033[0m %s\n", $$1, $$2 } /^##@/ { printf "\n\033[1m%s\033[0m\n", substr($$0, 5) } ' $(MAKEFILE_LIST)

##@ Setup

.PHONY: init
init: .env build up ## Full setup: env, build, up, composer install
	@echo -e "$(BLUE)Installing Composer dependencies...$(RESET)"
	@$(PHP_RUN) composer install --no-interaction
	@echo ""
	@echo -e "$(GREEN)Application is ready at http://localhost:$(NGINX_PORT)$(RESET)"
	@echo ""

.env:
	@echo -e "$(YELLOW)Creating .env from .env.example$(RESET)"
	@cp .env.example .env

##@ Development

.PHONY: up
up: ## Start dev containers
	@$(COMPOSE) up -d
	@echo -e "$(GREEN)Dev containers are running at http://localhost:$(NGINX_PORT)$(RESET)"

.PHONY: down
down: ## Stop dev containers
	@$(COMPOSE) down

.PHONY: restart
restart: ## Restart dev containers
	@$(COMPOSE) restart

.PHONY: build
build: ## Build dev images
	@$(COMPOSE) build

.PHONY: ps
ps: ## Show container status
	@$(COMPOSE) ps

.PHONY: logs
logs: ## Follow logs of all services
	@$(COMPOSE) logs -f

##@ Production

.PHONY: up-prod
up-prod: ## Start prod containers (build if needed)
	@$(COMPOSE_PROD) up -d --build
	@echo -e "$(GREEN)Prod containers are running at http://localhost:$(NGINX_PORT)$(RESET)"

.PHONY: down-prod
down-prod: ## Stop prod containers
	@$(COMPOSE_PROD) down

##@ Shells

.PHONY: sh
sh: ## Open bash in PHP container
	@$(PHP) bash

.PHONY: sh-db
sh-db: ## Open psql in PostgreSQL container
	@$(COMPOSE) exec postgres psql -U pizza -d pizza_shop

##@ Symfony

.PHONY: console
console: ## Run Symfony console: make console CMD="cache:clear"
	@$(CONSOLE) $(CMD)

.PHONY: composer
composer: ## Run composer: make composer CMD="require foo/bar"
	@$(COMPOSER) $(CMD)

.PHONY: migrate
migrate: ## Run DB migrations
	@$(CONSOLE) doctrine:migrations:migrate --no-interaction

.PHONY: migrate-diff
migrate-diff: ## Generate migration from entity diff
	@$(CONSOLE) doctrine:migrations:diff

.PHONY: cache-clear
cache-clear: ## Clear Symfony cache
	@$(CONSOLE) cache:clear

##@ Quality

.PHONY: test
test: ## Run test suite (placeholder until a later task)
	@echo -e "$(YELLOW)Tests will be added in a later task.$(RESET)"

##@ Cleanup

.PHONY: clean
clean: ## Stop containers and remove volumes (⚠ deletes DB data)
	@$(COMPOSE) down -v
	@echo -e "$(GREEN)Containers and volumes removed$(RESET)"

