.PHONY: help install test test-coverage fixtures build clean

# Default target
help:
	@echo "Available commands:"
	@echo "  install        - Install dependencies"
	@echo "  build          - Build the application"
	@echo "  test           - Run tests"
	@echo "  test-coverage  - Run tests with coverage report"
	@echo "  fixtures       - Load sample data"
	@echo "  clean          - Clear cache and logs"
	@echo "  migrate        - Run database migrations"
	@echo "  migrate-reset  - Reset database and run migrations"

# Install dependencies
install:
	docker-compose exec php bash -c "cd /home/wwwroot/app && composer install"

# Build the application
build:
	docker-compose build
	docker-compose up -d
	docker-compose exec php bash -c "cd /home/wwwroot/app && composer install"

# Run tests
test:
	docker-compose exec php bash -c "cd /home/wwwroot/app && php bin/phpunit"

# Run tests with coverage
test-coverage:
	docker-compose exec php bash -c "cd /home/wwwroot/app && php bin/phpunit --coverage-html var/coverage"

# Load fixtures
fixtures:
	docker-compose exec php bash -c "cd /home/wwwroot/app && php bin/console doctrine:fixtures:load --no-interaction"

# Clear cache and logs
clean:
	docker-compose exec php bash -c "cd /home/wwwroot/app && php bin/console cache:clear"
	docker-compose exec php bash -c "cd /home/wwwroot/app && rm -rf var/log/*"

# Run migrations
migrate:
	docker-compose exec php bash -c "cd /home/wwwroot/app && php bin/console doctrine:migrations:migrate --no-interaction"

# Reset database and run migrations
migrate-reset:
	docker-compose exec php bash -c "cd /home/wwwroot/app && php bin/console doctrine:database:drop --force --if-exists"
	docker-compose exec php bash -c "cd /home/wwwroot/app && php bin/console doctrine:database:create"
	docker-compose exec php bash -c "cd /home/wwwroot/app && php bin/console doctrine:migrations:migrate --no-interaction"

# Setup complete environment
setup: build migrate fixtures
	@echo "Setup complete! Access the application at http://localhost:8000"
	@echo "Admin login: admin@bugtracker.com / admin123" 