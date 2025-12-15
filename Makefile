.PHONY: help build up down restart logs shell composer npm artisan test migrate seed fresh setup clean

help: ## Show this help message
	@echo Usage: make [target]
	@echo.
	@echo Available targets:
	@echo   help       Show this help message
	@echo   build      Build all containers
	@echo   up         Start all containers in detached mode
	@echo   down       Stop all containers
	@echo   restart    Restart all containers
	@echo   logs       View logs from all containers
	@echo   shell      Access PHP container shell
	@echo   composer   Run composer install
	@echo   npm        Run npm install
	@echo   artisan    Run Laravel artisan commands (usage: make artisan cmd="migrate")
	@echo   test       Run PHPUnit tests
	@echo   migrate    Run database migrations
	@echo   seed       Run database seeders
	@echo   fresh      Run fresh migrations with seeders
	@echo   setup      Initial environment setup
	@echo   clean      Stop and remove all containers and volumes

build: ## Build all containers
	docker-compose build

up: ## Start all containers in detached mode
	docker-compose up -d

down: ## Stop all containers
	docker-compose down

restart: ## Restart all containers
	docker-compose restart

logs: ## View logs from all containers
	docker-compose logs -f

shell: ## Access PHP container shell
	docker-compose exec php sh

composer: ## Run composer install
	docker-compose exec php composer install

npm: ## Run npm install
	docker-compose exec node npm install

artisan: ## Run Laravel artisan commands (usage: make artisan cmd="migrate")
	docker-compose exec php php artisan $(cmd)

test: ## Run PHPUnit tests
	docker-compose exec php php artisan test

migrate: ## Run database migrations
	docker-compose exec php php artisan migrate

seed: ## Run database seeders
	docker-compose exec php php artisan db:seed

fresh: ## Run fresh migrations with seeders
	docker-compose exec php php artisan migrate:fresh --seed

setup: ## Initial environment setup (build, up, install dependencies, migrate)
	@echo Starting initial setup...
	docker-compose build
	docker-compose up -d
	@echo Waiting for services to be ready...
	timeout /t 10 /nobreak >nul
	docker-compose exec php composer install
	docker-compose exec node npm install
	docker-compose exec php php artisan key:generate
	docker-compose exec php php artisan migrate
	docker-compose exec php php artisan db:seed
	@echo.
	@echo Setup complete! Access the application at:
	@echo   Application: http://localhost
	@echo   phpMyAdmin:  http://localhost:8080
	@echo   Vite HMR:    http://localhost:5173

clean: ## Stop and remove all containers and volumes
	docker-compose down -v
