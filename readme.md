# Docker Symfony Starter Kit

Starter kit is based on [The perfect kit starter for a Symfony 4 project with Docker and PHP 7.2](https://medium.com/@romaricp/the-perfect-kit-starter-for-a-symfony-4-project-with-docker-and-php-7-2-fda447b6bca1).

## What is inside?

* Apache 2.4.57 (Debian)
* PHP 8.3 FPM
* MySQL 8.3.1
* NodeJS LTS (latest)
* Composer
* Symfony CLI 
* xdebug
* djfarrelly/maildev

## Requirements

* Install [Docker](https://www.docker.com/products/docker-desktop) and [Docker Compose](https://docs.docker.com/compose/install) on your machine 

## Installation

* (optional) Add 

```bash
127.0.0.1   symfony.local
```
in your `host` file.

* Run `build-env.sh` (or `build-env.ps1` on Windows box)

* Enter the PHP container:

```bash
docker-compose exec php bash
```

* To install Symfony LTS inside container execute:

```bash
cd app
rm .gitkeep
git config --global user.email "you@example.com"
symfony new ../app --version=lts --webapp
chown -R dev.dev *
```

## Container URLs and ports

* Project URL

```bash
http://localhost:8000
```

or 

```bash
http://symfony.local:8000
```

* MySQL

    * inside container: host is `mysql`, port: `3306`
    * outside container: host is `localhost`, port: `3307`
    * passwords, db name are in `docker-compose.yml`
    
* djfarrelly/maildev i available from the browser on port `8001`

* xdebug i available remotely on port `9000`

* Database connection in Symfony `.env` file:
```yaml
DATABASE_URL=mysql://symfony:symfony@mysql:3306/symfony?serverVersion=5.7
```

## Useful commands

* `docker-compose up -d` - start containers
* `docker-compose down` - stop containers
* `docker-compose exec php bash` - enter into PHP container
* `docker-compose exec mysql bash` - enter into MySQL container
* `docker-compose exec apache bash` - enter into Apache2 container

## Troubleshooting

* **2024.05.11 - ERROR: for apache  'ContainerConfig'**

  Error `ERROR: for apache  'ContainerConfig'` after `docker-compose up -d` execution can be solved `docker compose up -d --force-recreate`

# Bug Tracker Board

A modern bug tracking application built with Symfony 6.4, featuring role-based access control, issue management, and a responsive Bootstrap UI.

## Quick Start

### Prerequisites
- Docker and Docker Compose
- Git

### Installation
```bash
# Clone the repository
git clone <repository-url>
cd PHP_Bug_Tracker

# Start the application
docker-compose up -d

# Install dependencies and setup database
cd app
make setup
```

### Access the Application
- **Application**: http://localhost:8000
- **Default Admin**: admin@bugtracker.com / admin123

## Features

### User Management
- **Admin**: Full CRUD access to issues and categories, profile management
- **Guest**: Read-only access to view issues

### Issue Management
- Create, read, update, delete issues
- Status tracking (open, in progress, closed)
- Priority levels (low, medium, high)
- Category organization
- Sorting and filtering

### Category Management
- Organize issues by categories
- Admin-only management
- Prevents deletion of categories with associated issues

### Profile Management
- View and edit personal information
- Change password functionality
- Secure authentication

### Modern UI
- Responsive Bootstrap 5.3 design
- Clean, intuitive interface
- Mobile-friendly navigation

## Development

### Available Commands
```bash
make help           # Show all available commands
make build          # Build Docker containers
make install        # Install dependencies
make test           # Run tests
make fixtures       # Load sample data
make clean          # Clear cache
make migrate        # Run database migrations
```

### Code Quality
- PHP CS Fixer for code formatting
- PHPUnit for testing
- Symfony best practices

## Security

- Form-based authentication
- Role-based access control
- CSRF protection
- Password hashing
- Input validation

## Database Schema

### AdminUser
- Email (unique)
- Password (hashed)
- First name, last name
- Roles (JSON)

### Category
- Name
- Description (optional)

### Issue
- Title
- Description
- Status (enum)
- Priority (enum)
- Category (foreign key)
- Created date

## Project Structure

```
PHP_Bug_Tracker/
├── app/                    # Symfony application
│   ├── src/
│   │   ├── Controller/     # Application controllers
│   │   ├── Entity/         # Doctrine entities
│   │   ├── Repository/     # Data access layer
│   │   ├── Form/           # Form types
│   │   ├── Service/        # Business logic
│   │   └── DataFixtures/   # Sample data
│   ├── templates/          # Twig templates
│   ├── tests/              # Test suite
│   ├── migrations/         # Database migrations
│   └── config/             # Configuration
├── docker-compose.yml      # Docker services
└── README.md              # This file
```

## Bug Reports

This is a bug tracking application itself! Use the application to report issues.

## License

This project is proprietary software.

---

**Built with Symfony 6.4**

  


