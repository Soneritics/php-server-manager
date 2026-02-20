# Research: Docker Development/Testing Container

**Date**: 2025-01-21  
**Feature**: 001-docker-dev-container  
**Purpose**: Resolve technical unknowns identified in Technical Context

## Research Tasks Completed

1. ✅ Nginx + PHP-FPM container setup patterns
2. ✅ Test data generation using inline shell commands
3. ✅ Multi-process container management without supervisord

---

## Decision 1: Container Base Image and Architecture

### Decision
Use **php:8.2-fpm-alpine** as the base image and install Nginx separately within the same container.

### Rationale
- **Alpine Linux**: Minimal footprint (~80MB vs 300MB+ for Debian-based images), faster builds, suitable for development containers
- **php:fpm-alpine**: Official PHP image with FPM pre-configured, reduces setup complexity
- **Single container**: Simpler for developers to manage (single build, single run command), meets FR-009 requirement for "standard container runtime commands"
- **Separate Nginx installation**: Full control over web server configuration while leveraging official PHP-FPM setup

### Alternatives Considered
- **Debian-based php:fpm**: Rejected due to larger image size (300MB+) and slower builds, no advantage for development use case
- **Ubuntu base + manual PHP/Nginx install**: Rejected due to complexity and slower builds, violates "usability over performance" constitution principle by making setup harder
- **Pre-combined images (webdevops/php-nginx)**: Rejected to maintain transparency and educational value, aligns with "working code over quality" by keeping setup understandable

---

## Decision 2: Multi-Process Management Strategy

### Decision
Use a **simple bash entrypoint script** that:
1. Starts PHP-FPM in background (`php-fpm --nodaemonize &`)
2. Starts Nginx in foreground using `exec` (`exec nginx -g "daemon off;"`)
3. No supervisord or other process managers

### Rationale
- **FR-003 compliance**: Feature spec explicitly requires "shell script as ENTRYPOINT that launches both processes"
- **Simplicity**: 5-line script vs supervisord dependency + configuration files
- **Signal handling**: Using `exec` makes Nginx PID 1, signals propagate naturally for clean shutdown
- **Constitution alignment**: "Working code over quality" - simple script over robust process manager for dev containers

### Alternatives Considered
- **supervisord**: Rejected per feature spec FR-003, adds unnecessary complexity and dependencies for development use case
- **systemd**: Rejected as not available in Alpine containers, significant overhead
- **Foreground both processes with wait**: Rejected because signal handling is complex and error-prone without proper trap handlers

### Implementation Pattern
```bash
#!/bin/bash
set -e

php-fpm --nodaemonize &
exec nginx -g "daemon off;"
```

**Key benefits**:
- Nginx becomes PID 1, receives Docker signals (SIGTERM on stop)
- Container exits when Nginx exits (acceptable for dev)
- PHP-FPM backgrounded but shares container lifecycle
- `--nodaemonize` keeps PHP-FPM running (default daemonizes and exits)
- `daemon off;` keeps Nginx in foreground (default backgrounds)

---

## Decision 3: Test Data Generation Approach

### Decision
Generate all test data using **inline Dockerfile RUN commands** with shell utilities (mkdir, echo, dd, cat, heredoc).

### Rationale
- **FR-008 compliance**: "Container definition file MUST be self-contained and not require external files beyond server-manager.php"
- **Single RUN layer**: Combine all test data generation into one RUN statement to minimize Docker image layers (better performance)
- **Constitution alignment**: "Native methods only" - uses only standard shell commands, no external scripts or tools

### Alternatives Considered
- **Separate shell script**: Rejected per FR-008, would require external file
- **COPY from external directory**: Rejected per FR-008, violates self-contained requirement
- **Multiple RUN statements**: Rejected due to Docker layer bloat (each RUN = new layer increases image size)

### Implementation Patterns

**Directory structure (5 levels, brace expansion)**:
```dockerfile
RUN mkdir -p /var/www/test-data/{level1/{level2/{level3/{level4/{level5,level5-alt},level4-alt},level3-alt},level2-alt},level1-alt}
```

**Files with varying sizes**:
```bash
# 1KB file
dd if=/dev/zero of=/path/file.bin bs=1K count=1 2>/dev/null

# 100KB file
dd if=/dev/zero of=/path/file.bin bs=100K count=1 2>/dev/null

# 1MB file
dd if=/dev/zero of=/path/file.bin bs=1M count=1 2>/dev/null
```

**Files with content (heredoc)**:
```bash
cat > /path/config.ini <<'EOF'
[section]
key=value
EOF
```

**Permissions and ownership**:
```bash
chmod 644 /path/file.txt
chmod 755 /path/script.sh
chmod 600 /path/secret.key
chown www-data:www-data /path/file.txt
```

**Combined single RUN layer strategy**:
```dockerfile
RUN mkdir -p /var/www/test-data/{dirs...} && \
    echo "content" > /var/www/test-data/file1.txt && \
    dd if=/dev/zero of=/var/www/test-data/large.bin bs=1M count=1 2>/dev/null && \
    chmod 644 /var/www/test-data/file1.txt && \
    chmod 600 /var/www/test-data/secret.key && \
    chown root:root /var/www/test-data/file1.txt && \
    chown www-data:www-data /var/www/test-data/web-file.txt
```

---

## Decision 4: Nginx Configuration Strategy

### Decision
Create a **minimal, inline Nginx configuration** file that:
- Listens on port 80
- Serves PHP files through FastCGI to PHP-FPM Unix socket
- Sets document root to `/var/www/html` (PHP Server Manager location)
- Includes basic MIME types and FastCGI parameters

### Rationale
- **Minimal configuration**: Development container needs only basic PHP file serving, no SSL/caching/compression
- **Unix socket communication**: More efficient than TCP for same-host PHP-FPM communication
- **Constitution alignment**: "Usability over performance" - simple config that developers can understand and modify

### Implementation
```nginx
user www-data;
worker_processes auto;

events {
    worker_connections 1024;
}

http {
    include /etc/nginx/mime.types;
    default_type application/octet-stream;

    server {
        listen 80;
        server_name _;
        root /var/www/html;
        index server-manager.php;

        location ~ \.php$ {
            fastcgi_pass unix:/run/php-fpm/php-fpm.sock;
            fastcgi_index server-manager.php;
            include fastcgi_params;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        }
    }
}
```

### Alternatives Considered
- **TCP socket (127.0.0.1:9000)**: Rejected because Unix socket is more efficient and standard for single-container setups
- **Reusing default nginx.conf**: Rejected because default configs often include unnecessary complexity
- **Separate config files per directive**: Rejected to keep configuration simple and inline-readable

---

## Decision 5: File Permissions and Ownership Requirements

### Decision
Implement **two-tier ownership model** for test data:
- **root (UID 0)**: Configuration files, system-level test files
- **www-data**: Web-accessible files, writable directories

Implement **three permission configurations**:
- **644 (rw-r--r--)**: Regular files (configs, logs)
- **755 (rwxr-xr-x)**: Executable scripts, directories
- **600 (rw-------)**: Sensitive files (secrets, restricted configs)

### Rationale
- **FR-004 compliance**: Requirement specifies "at least 3 different file permission configurations" and "at least 2 different file ownership configurations"
- **Realistic web server scenarios**: Mirrors production environments where some files are system-owned (root) and others web-owned (www-data)
- **Testing value**: Enables testing of PHP Server Manager's permission display and access behaviors

### Implementation Pattern
```bash
# Root-owned system files
echo "system config" > /var/www/test-data/system.conf
chmod 644 /var/www/test-data/system.conf
chown root:root /var/www/test-data/system.conf

# www-data-owned web files
echo "web content" > /var/www/test-data/web.txt
chmod 644 /var/www/test-data/web.txt
chown www-data:www-data /var/www/test-data/web.txt

# Executable script
echo "#!/bin/bash" > /var/www/test-data/script.sh
chmod 755 /var/www/test-data/script.sh

# Restricted file
echo "secret" > /var/www/test-data/secret.key
chmod 600 /var/www/test-data/secret.key
```

---

## Decision 6: Volume Mounting Strategy for Development

### Decision
Support **optional volume mounting** of `server-manager.php` for iterative development using standard Docker `-v` flag:
```bash
docker run -v ./server-manager.php:/var/www/html/server-manager.php:ro -p 8080:80 php-server-manager
```

### Rationale
- **FR-007 compliance**: "Container MUST support mounting the server-manager.php file as a volume"
- **SC-005 compliance**: "Code changes reflected in browser within 2 seconds"
- **Development workflow**: Enables edit-refresh cycle without rebuilding container
- **No code changes needed**: Standard Docker feature, no special container logic required

### Alternatives Considered
- **Always volume-mounted**: Rejected because container should work standalone for testing without local PHP file
- **Copy-on-build only**: Rejected because it would require rebuilding for every code change, violating FR-007
- **Writable mount**: Rejected in favor of read-only (`:ro`) for safety - container shouldn't modify host files

---

## Research Validation Checklist

- ✅ All Technical Context "NEEDS CLARIFICATION" items resolved
- ✅ Base image selected (php:8.2-fpm-alpine)
- ✅ Multi-process strategy defined (shell entrypoint)
- ✅ Test data generation approach documented (inline RUN commands)
- ✅ Nginx configuration pattern established
- ✅ Permission/ownership model specified
- ✅ Volume mounting strategy confirmed
- ✅ All decisions aligned with constitution principles
- ✅ All decisions traceable to feature requirements

---

## Next Steps (Phase 1)

1. Create data model defining container entities and test data structure
2. Generate Nginx configuration contract specification
3. Create quickstart guide with Docker build/run examples
4. Update Technical Context in plan.md if any unknowns remain (none expected)
