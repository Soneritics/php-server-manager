# Data Model: Docker Development/Testing Container

**Feature**: 001-docker-dev-container  
**Date**: 2025-01-21  
**Purpose**: Define entities, structures, and relationships for container implementation

---

## Entity 1: Container Image

**Purpose**: Packaged, executable artifact containing PHP, Nginx, PHP-FPM, test data, and configuration.

### Attributes

| Attribute | Type | Constraints | Description |
|-----------|------|-------------|-------------|
| base_image | string | `php:8.2-fpm-alpine` (fixed) | Official PHP-FPM Alpine image |
| php_version | string | `8.2+` | PHP version from base image |
| nginx_version | string | Latest from Alpine repos | Nginx web server version |
| image_size | integer | Target: <150MB | Total Docker image size after build |
| build_time | duration | Target: <300 seconds | Time to complete `docker build` |
| exposed_ports | list[integer] | `[80]` | Network ports exposed to host |

### State Transitions

```
[Source Code + Dockerfile] 
    → docker build → 
[Built Image] 
    → docker run → 
[Running Container]
    → docker stop →
[Stopped Container]
```

### Validation Rules

- Base image MUST be official PHP image from Docker Hub
- Image MUST contain exactly one entrypoint script
- Image MUST expose port 80
- Image MUST include both Nginx and PHP-FPM binaries
- Image MUST be buildable without external network dependencies beyond base image pull

---

## Entity 2: Test Data Structure

**Purpose**: Pre-populated filesystem hierarchy providing realistic testing scenarios for PHP Server Manager.

### Attributes

| Attribute | Type | Constraints | Description |
|-----------|------|-------------|-------------|
| root_path | string | `/var/www/test-data` | Base directory for all test files |
| directory_depth | integer | Exactly 5 levels | Maximum nesting depth (FR-004) |
| total_files | integer | 30-50 files | Total file count across all directories (FR-004) |
| total_directories | integer | ~15-20 directories | Supporting 5-level structure |

### Directory Structure Schema

```
/var/www/test-data/
├── level1-configs/              # Level 1: Configuration files
│   ├── app.conf                 # 1KB, 644, root:root
│   ├── database.ini             # 1KB, 644, root:root
│   └── level2-services/         # Level 2: Service configs
│       ├── web.conf             # 1KB, 644, www-data:www-data
│       ├── api.conf             # 1KB, 644, www-data:www-data
│       └── level3-overrides/    # Level 3: Environment overrides
│           ├── dev.env          # 1KB, 644, root:root
│           ├── test.env         # 1KB, 644, root:root
│           └── level4-secrets/  # Level 4: Sensitive configs
│               ├── api.key      # 1KB, 600, root:root
│               ├── db.creds     # 1KB, 600, root:root
│               └── level5-certs/ # Level 5: Certificates
│                   ├── ca.crt   # 1KB, 644, root:root
│                   └── server.pem # 1KB, 600, root:root
├── level1-logs/                 # Level 1: Log files
│   ├── app.log                  # 100KB, 644, www-data:www-data
│   ├── error.log                # 100KB, 644, www-data:www-data
│   └── level2-archives/         # Level 2: Archived logs
│       ├── 2024-01.log.gz       # 1MB, 644, www-data:www-data
│       ├── 2024-02.log.gz       # 1MB, 644, www-data:www-data
│       └── level3-backup/       # Level 3: Log backups
│           ├── backup-01.tar    # 1MB, 644, root:root
│           └── level4-checksums/ # Level 4: Integrity files
│               ├── sha256sums.txt # 1KB, 644, root:root
│               └── level5-meta/  # Level 5: Metadata
│                   └── manifest.json # 1KB, 644, root:root
├── level1-scripts/              # Level 1: Executable scripts
│   ├── deploy.sh                # 1KB, 755, root:root
│   ├── backup.sh                # 1KB, 755, root:root
│   └── level2-utils/            # Level 2: Utility scripts
│       ├── db-migrate.sh        # 1KB, 755, www-data:www-data
│       ├── cache-clear.sh       # 1KB, 755, www-data:www-data
│       └── level3-hooks/        # Level 3: Lifecycle hooks
│           ├── pre-deploy.sh    # 1KB, 755, root:root
│           ├── post-deploy.sh   # 1KB, 755, root:root
│           └── level4-validators/ # Level 4: Validation scripts
│               ├── syntax-check.sh # 1KB, 755, root:root
│               └── level5-reports/ # Level 5: Report generators
│                   └── status.sh # 1KB, 755, root:root
└── level1-data/                 # Level 1: Data files
    ├── users.json               # 10KB, 644, www-data:www-data
    ├── products.json            # 10KB, 644, www-data:www-data
    └── level2-cache/            # Level 2: Cached data
        ├── sessions.db          # 100KB, 644, www-data:www-data
        ├── query-cache.db       # 100KB, 644, www-data:www-data
        └── level3-temp/         # Level 3: Temporary files
            ├── upload-001.tmp   # 1KB, 644, www-data:www-data
            ├── upload-002.tmp   # 1KB, 644, www-data:www-data
            └── level4-processing/ # Level 4: Processing queue
                ├── queue-001.json # 1KB, 644, www-data:www-data
                └── level5-results/ # Level 5: Processed results
                    └── result-001.json # 1KB, 644, www-data:www-data
```

**Total Counts**:
- Directories: 20 (4 main branches × 5 levels)
- Files: 40 (distributed across all levels)
- Depth: Exactly 5 levels (meets FR-004)

### File Type Distribution

| Type | Extension | Count | Purpose |
|------|-----------|-------|---------|
| Configuration | `.conf`, `.ini`, `.env` | 8 | App/service configs |
| Logs | `.log`, `.log.gz` | 4 | Application logs |
| Archives | `.tar`, `.gz` | 3 | Compressed backups |
| Scripts | `.sh` | 10 | Executable scripts |
| Data | `.json`, `.db` | 8 | Application data |
| Secrets | `.key`, `.creds`, `.pem`, `.crt` | 4 | Sensitive files |
| Metadata | `.txt`, `.json` | 3 | Checksums, manifests |

### File Size Distribution

| Size | Count | Use Case |
|------|-------|----------|
| 1KB | 28 | Small configs, scripts, keys |
| 10KB | 2 | Medium data files |
| 100KB | 6 | Log files, cache databases |
| 1MB | 4 | Large archives, backups |

### Permission Distribution

| Permission | Octal | Count | Applied To | Owner:Group |
|------------|-------|-------|------------|-------------|
| Read/write (owner only) | 600 | 4 | Secrets (keys, creds, certs) | root:root |
| Read/write (owner), read (group/other) | 644 | 26 | Configs, logs, data | Mixed |
| Execute (all), write (owner) | 755 | 10 | Scripts | Mixed |

### Ownership Distribution

| Owner:Group | Count | File Types |
|-------------|-------|------------|
| root:root | 20 | System configs, scripts, secrets |
| www-data:www-data | 20 | Web-accessible files, logs, cache |

### Validation Rules

- **FR-004 Depth**: All 4 directory branches MUST reach exactly level 5
- **FR-004 File Count**: Total files MUST be between 30-50
- **FR-004 Size Variety**: MUST include files of 1KB, 100KB, and 1MB minimum
- **FR-004 Permissions**: MUST include at least 3 permission configs (600, 644, 755)
- **FR-004 Ownership**: MUST include both root:root and www-data:www-data ownership
- Test data MUST be generated inline in Dockerfile (FR-008)
- Test data MUST persist for container lifetime (FR-011)

---

## Entity 3: Container Process

**Purpose**: Running instance of the container image with active Nginx and PHP-FPM processes.

### Attributes

| Attribute | Type | Constraints | Description |
|-----------|------|-------------|-------------|
| container_id | string | Docker-generated | Unique container identifier |
| status | enum | `starting`, `running`, `stopping`, `stopped` | Container lifecycle state |
| nginx_pid | integer | PID 1 (via exec) | Nginx master process ID |
| php_fpm_pid | integer | Background process | PHP-FPM master process ID |
| start_time | duration | Target: <10 seconds | Time from `docker run` to HTTP responsive |
| port_mapping | map | Host port → 80 | External port mapping |
| mounted_volumes | list[string] | Optional | Volume mount specifications |

### State Transitions

```
docker run
    ↓
[starting]
    ↓ (entrypoint.sh executes)
PHP-FPM starts (background)
    ↓
Nginx starts (foreground)
    ↓ (HTTP port 80 listening)
[running]
    ↓ (docker stop)
SIGTERM → Nginx
    ↓
[stopping]
    ↓ (graceful shutdown)
[stopped]
```

### Process Hierarchy

```
PID 1: nginx (master)
    ├── nginx (worker 1)
    ├── nginx (worker 2)
    └── ...
PID X: php-fpm (master) [background]
    ├── php-fpm (worker 1)
    ├── php-fpm (worker 2)
    └── ...
```

### Validation Rules

- Nginx MUST be PID 1 (via `exec` in entrypoint)
- PHP-FPM MUST start before Nginx
- Container MUST respond to HTTP requests within 10 seconds of start (SC-002)
- Container MUST handle SIGTERM gracefully (stop cleanly)
- PHP-FPM socket `/run/php-fpm/php-fpm.sock` MUST exist when Nginx starts

---

## Entity 4: Nginx Configuration

**Purpose**: Web server configuration defining how Nginx serves PHP Server Manager and communicates with PHP-FPM.

### Attributes

| Attribute | Type | Value | Description |
|-----------|------|-------|-------------|
| listen_port | integer | 80 | HTTP port |
| server_name | string | `_` (catch-all) | Virtual host name |
| document_root | string | `/var/www/html` | Web root directory |
| index_file | string | `server-manager.php` | Default file |
| fastcgi_socket | string | `unix:/run/php-fpm/php-fpm.sock` | PHP-FPM communication |
| user | string | `www-data` | Nginx worker process user |
| worker_processes | string | `auto` | Number of worker processes |

### Configuration Schema

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

### Validation Rules

- Configuration MUST listen on port 80 (FR-006)
- Configuration MUST serve PHP files via FastCGI (FR-002)
- Configuration MUST use Unix socket for PHP-FPM communication
- Configuration MUST set document root to `/var/www/html` (FR-005)
- Configuration file MUST be valid (`nginx -t` passes)

---

## Entity 5: Entrypoint Script

**Purpose**: Shell script that orchestrates multi-process startup in the container.

### Attributes

| Attribute | Type | Value | Description |
|-----------|------|-------------|-------------|
| interpreter | string | `#!/bin/bash` | Shell interpreter |
| php_fpm_command | string | `php-fpm --nodaemonize` | PHP-FPM start command |
| nginx_command | string | `exec nginx -g "daemon off;"` | Nginx start command (replaces shell) |
| error_handling | boolean | `set -e` | Exit on command failure |

### Script Schema

```bash
#!/bin/bash
set -e

# Start PHP-FPM in background
php-fpm --nodaemonize &

# Start Nginx in foreground (becomes PID 1)
exec nginx -g "daemon off;"
```

### Validation Rules

- Script MUST be executable (chmod +x)
- Script MUST start PHP-FPM before Nginx (FR-003)
- Script MUST keep container alive (FR-003)
- Script MUST use `--nodaemonize` for PHP-FPM (prevents premature exit)
- Script MUST use `daemon off;` for Nginx (prevents backgrounding)
- Script MUST use `exec` for Nginx (signal handling)

---

## Entity 6: Volume Mount (Optional)

**Purpose**: Developer-specified mapping of host filesystem to container for iterative development.

### Attributes

| Attribute | Type | Constraints | Description |
|-----------|------|-------------|-------------|
| host_path | string | Relative or absolute | Path to server-manager.php on host |
| container_path | string | `/var/www/html/server-manager.php` | Target path in container |
| mode | enum | `ro` (read-only) | Mount mode (safety) |
| sync_latency | duration | <2 seconds | Time for changes to reflect in browser |

### Mount Specification

```bash
# Docker CLI format
-v ./server-manager.php:/var/www/html/server-manager.php:ro

# Docker Compose format
volumes:
  - ./server-manager.php:/var/www/html/server-manager.php:ro
```

### Validation Rules

- Mount MUST be optional (container works without it)
- Mount SHOULD be read-only for safety
- Host file MUST exist before container start
- Changes to host file MUST be visible on page refresh (SC-005)
- Mount MUST NOT break container isolation (FR-010)

---

## Relationships Between Entities

```
Container Image
    ├── contains → Nginx Configuration (embedded)
    ├── contains → Entrypoint Script (embedded)
    ├── contains → Test Data Structure (embedded)
    └── instantiates → Container Process (runtime)

Container Process
    ├── executes → Entrypoint Script
    ├── runs → Nginx (PID 1)
    ├── runs → PHP-FPM (background)
    ├── serves → server-manager.php
    ├── exposes → port 80
    └── optionally loads → Volume Mount

Test Data Structure
    ├── accessed by → PHP Server Manager
    ├── displayed in → Browser Interface
    └── persists in → Container Filesystem

Volume Mount
    ├── overrides → server-manager.php in image
    └── enables → Iterative Development
```

---

## Data Validation Summary

### Build-time Validations
- ✅ Base image pullable from Docker Hub
- ✅ Test data structure meets FR-004 requirements (depth, count, variety)
- ✅ Nginx configuration syntax valid (`nginx -t`)
- ✅ Entrypoint script executable
- ✅ All required files present in image

### Runtime Validations
- ✅ Container starts within 10 seconds (SC-002)
- ✅ Port 80 accessible from host
- ✅ PHP-FPM socket exists at `/run/php-fpm/php-fpm.sock`
- ✅ Nginx serves server-manager.php successfully
- ✅ Test data browsable through PHP Server Manager interface

### Development Workflow Validations
- ✅ Volume mount works (if specified)
- ✅ Code changes reflect in <2 seconds (SC-005)
- ✅ Container stops cleanly with `docker stop`
- ✅ Container restarts successfully with `docker restart`

---

## Next Steps

1. ✅ Data model complete
2. → Generate contract specifications (Nginx config, entrypoint script)
3. → Create quickstart guide with build/run examples
4. → Update agent context with new container technology stack
