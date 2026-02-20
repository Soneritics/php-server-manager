# php-server-manager Development Guidelines

Auto-generated from all feature plans. Last updated: 2026-02-19

## Active Technologies
- PHP 8.2+ (latest stable from official Docker images), Bash (for entrypoint script) + Nginx (web server), PHP-FPM (PHP FastCGI Process Manager) - both from official repositories (001-docker-dev-container)
- Container filesystem - ephemeral (recreated on each build), test data inline-generated via Dockerfile RUN commands (001-docker-dev-container)

- PHP 5.4+ (server-side scripting) + None - native PHP functions only (per constitution Principle III) (001-php-server-manager)

## Project Structure

```text
backend/
frontend/
tests/
```

## Commands

# Add commands for PHP 5.4+ (server-side scripting)

## Code Style

PHP 5.4+ (server-side scripting): Follow standard conventions

## Recent Changes
- 001-docker-dev-container: Added PHP 8.2+ (latest stable from official Docker images), Bash (for entrypoint script) + Nginx (web server), PHP-FPM (PHP FastCGI Process Manager) - both from official repositories

- 001-php-server-manager: Added PHP 5.4+ (server-side scripting) + None - native PHP functions only (per constitution Principle III)

<!-- MANUAL ADDITIONS START -->
<!-- MANUAL ADDITIONS END -->
