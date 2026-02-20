# Docker Guide

A Docker container is provided for local development and testing. It includes a pre-populated test environment with sample files and directories.

> **Warning:** The Docker container is for **development and testing only**. Do not deploy it to production.

## Prerequisites

- [Docker](https://docs.docker.com/get-docker/) installed
- Port 8080 available (or choose a different port)

Verify Docker is installed:

```bash
docker --version
```

## Quick Start

### Build

```bash
docker build -t php-server-manager .
```

Build takes ~2–3 minutes on first run, ~30 seconds on subsequent builds.

### Run

```bash
docker run -d -p 8080:80 --name php-server-manager-test php-server-manager
```

### Access

Open `http://localhost:8080` in your browser.

**Default password:** `change-this-password-before-deployment`

## Development Workflow

Mount your local file for live editing — changes appear instantly on browser refresh without rebuilding the container:

```bash
docker run -d -p 8080:80 \
  -v ./server-manager.php:/var/www/html/server-manager.php:ro \
  --name php-server-manager-test \
  php-server-manager
```

The `:ro` flag mounts the file as read-only so the container cannot modify your local file.

**Workflow:**

1. Edit `server-manager.php` in your editor
2. Save the file
3. Refresh the browser
4. Changes appear immediately

## Test Data

The container includes pre-populated test data at `/var/www/test-data/`:

- **40+ files** across 5 directory levels
- **File types:** `.conf`, `.ini`, `.log`, `.sh`, `.json`, `.db`, `.crt`, `.pem`
- **Permissions:** `600` (secrets), `644` (configs), `755` (scripts)
- **Ownership:** Mix of `root:root` and `www-data:www-data`
- **File sizes:** Range from small config files to 1MB log archives

Navigate to `/var/www/test-data` in the console to explore.

## Container Management

```bash
# Stop
docker stop php-server-manager-test

# Start
docker start php-server-manager-test

# Restart
docker restart php-server-manager-test

# View logs
docker logs php-server-manager-test

# Remove
docker rm -f php-server-manager-test
```

## Interactive Shell

Access a shell inside the running container:

```bash
docker exec -it php-server-manager-test /bin/sh
```

Useful for inspecting file permissions, testing PHP commands, or debugging configuration issues.

## Troubleshooting

### Port Already in Use

Use a different port:

```bash
docker run -d -p 9090:80 --name php-server-manager-test php-server-manager
```

Access at `http://localhost:9090`.

### Container Exits Immediately

Check logs:

```bash
docker logs php-server-manager-test
```

Common causes: PHP-FPM failed to start, Nginx configuration error.

### Cannot Access in Browser

Verify the container is running:

```bash
docker ps
```

Test with curl:

```bash
curl http://localhost:8080
```

### Volume Mount Not Working

Verify the file exists locally and check the mount inside the container:

```bash
docker exec php-server-manager-test ls -la /var/www/html/
```

## Container Architecture

The container runs:

- **Base image:** PHP 8.2 FPM on Alpine Linux
- **Web server:** Nginx (port 80)
- **PHP runtime:** PHP-FPM (FastCGI)
- **Entrypoint:** Starts both PHP-FPM and Nginx

## Build Times

| Operation | First Build | Subsequent |
|-----------|-------------|------------|
| `docker build` | ~2–3 min | ~30 sec |
| `docker run` | ~5–10 sec | ~5–10 sec |
| Total to browser | ~3–4 min | ~1 min |
