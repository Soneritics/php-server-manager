# Docker Development/Testing Container for PHP Server Manager
# Base image: PHP 8.2 with PHP-FPM on Alpine Linux
FROM php:8.2-fpm-alpine

# Install Nginx web server
RUN apk add --no-cache nginx

# Create required directories for PHP-FPM socket and web content
RUN mkdir -p /run/php-fpm /var/www/html

# Configure Nginx using printf (inline configuration)
RUN printf 'user www-data;\nworker_processes auto;\n\nevents {\n    worker_connections 1024;\n}\n\nhttp {\n    include /etc/nginx/mime.types;\n    default_type application/octet-stream;\n\n    server {\n        listen 80;\n        server_name _;\n        root /var/www/html;\n        index server-manager.php;\n\n        location ~ \\.php$ {\n            fastcgi_pass 127.0.0.1:9000;\n            fastcgi_index server-manager.php;\n            include fastcgi_params;\n            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;\n        }\n    }\n}\n' > /etc/nginx/nginx.conf

# Validate Nginx configuration
RUN nginx -t

# Create entrypoint script using printf (inline script)
RUN printf '#!/bin/sh\nset -e\n\n# Start PHP-FPM in background\nphp-fpm --nodaemonize &\n\n# Start Nginx in foreground (becomes PID 1)\nexec nginx -g "daemon off;"\n' > /usr/local/bin/docker-entrypoint.sh

# Make entrypoint script executable
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Create test data directory structure (5 levels deep) - T020
RUN mkdir -p /var/www/test-data/level1-configs/level2-services/level3-overrides/level4-secrets/level5-certs && \
    mkdir -p /var/www/test-data/level1-logs/level2-archives/level3-backup/level4-checksums/level5-meta && \
    mkdir -p /var/www/test-data/level1-scripts/level2-utils/level3-hooks/level4-validators/level5-reports && \
    mkdir -p /var/www/test-data/level1-data/level2-cache/level3-temp/level4-processing/level5-results

# Generate test files for all branches (T021-T024 in parallel)
# Level 1-5 configs branch (8 files)
RUN printf 'app_name=server-manager\nlog_level=debug\n' > /var/www/test-data/level1-configs/app.conf && \
    printf '[database]\nhost=localhost\nport=3306\n' > /var/www/test-data/level1-configs/database.ini && \
    printf 'server_port=80\nworker_processes=auto\n' > /var/www/test-data/level1-configs/level2-services/web.conf && \
    printf 'api_endpoint=/api/v1\ntimeout=30\n' > /var/www/test-data/level1-configs/level2-services/api.conf && \
    printf 'ENV=development\nDEBUG=true\n' > /var/www/test-data/level1-configs/level2-services/level3-overrides/dev.env && \
    printf 'ENV=testing\nDEBUG=false\n' > /var/www/test-data/level1-configs/level2-services/level3-overrides/test.env && \
    printf 'API_KEY=sk_test_1234567890abcdef\n' > /var/www/test-data/level1-configs/level2-services/level3-overrides/level4-secrets/api.key && \
    printf 'DB_USER=admin\nDB_PASS=secret123\n' > /var/www/test-data/level1-configs/level2-services/level3-overrides/level4-secrets/db.creds && \
    printf -- '-----BEGIN CERTIFICATE-----\nMIIDXTCCAkWgAwIBAgIJAKZ\n-----END CERTIFICATE-----\n' > /var/www/test-data/level1-configs/level2-services/level3-overrides/level4-secrets/level5-certs/ca.crt && \
    printf -- '-----BEGIN PRIVATE KEY-----\nMIIEvQIBADANBgkqhkiG9w0BAQ\n-----END PRIVATE KEY-----\n' > /var/www/test-data/level1-configs/level2-services/level3-overrides/level4-secrets/level5-certs/server.pem

# Level 1-5 logs branch (10 files) - Create larger files
RUN dd if=/dev/zero bs=100k count=1 2>/dev/null | tr '\0' 'A' > /var/www/test-data/level1-logs/app.log && \
    dd if=/dev/zero bs=100k count=1 2>/dev/null | tr '\0' 'E' > /var/www/test-data/level1-logs/error.log && \
    dd if=/dev/zero bs=1M count=1 2>/dev/null | tr '\0' 'L' > /var/www/test-data/level1-logs/level2-archives/2024-01.log.gz && \
    dd if=/dev/zero bs=1M count=1 2>/dev/null | tr '\0' 'L' > /var/www/test-data/level1-logs/level2-archives/2024-02.log.gz && \
    dd if=/dev/zero bs=1M count=1 2>/dev/null | tr '\0' 'B' > /var/www/test-data/level1-logs/level2-archives/level3-backup/backup-01.tar && \
    printf 'file1.log: sha256sum1234567890abcdef\nfile2.log: sha256sum0987654321fedcba\n' > /var/www/test-data/level1-logs/level2-archives/level3-backup/level4-checksums/sha256sums.txt && \
    printf '{"backup_date":"2024-01-15","files":["app.log","error.log"],"size_mb":150}\n' > /var/www/test-data/level1-logs/level2-archives/level3-backup/level4-checksums/level5-meta/manifest.json

# Level 1-5 scripts branch (10 files)
RUN printf '#!/bin/sh\necho "Deploying application..."\ncd /var/www/html\nphp artisan migrate\n' > /var/www/test-data/level1-scripts/deploy.sh && \
    printf '#!/bin/sh\necho "Creating backup..."\ntar -czf backup.tar.gz /var/www/html\n' > /var/www/test-data/level1-scripts/backup.sh && \
    printf '#!/bin/sh\necho "Running database migrations..."\nphp artisan migrate --force\n' > /var/www/test-data/level1-scripts/level2-utils/db-migrate.sh && \
    printf '#!/bin/sh\necho "Clearing cache..."\nphp artisan cache:clear\n' > /var/www/test-data/level1-scripts/level2-utils/cache-clear.sh && \
    printf '#!/bin/sh\necho "Running pre-deployment checks..."\nnginx -t\n' > /var/www/test-data/level1-scripts/level2-utils/level3-hooks/pre-deploy.sh && \
    printf '#!/bin/sh\necho "Running post-deployment tasks..."\nphp artisan queue:restart\n' > /var/www/test-data/level1-scripts/level2-utils/level3-hooks/post-deploy.sh && \
    printf '#!/bin/sh\necho "Checking PHP syntax..."\nfind . -name "*.php" -exec php -l {} \\;\n' > /var/www/test-data/level1-scripts/level2-utils/level3-hooks/level4-validators/syntax-check.sh && \
    printf '#!/bin/sh\necho "Validating configuration..."\nnginx -t && php-fpm -t\n' > /var/www/test-data/level1-scripts/level2-utils/level3-hooks/level4-validators/config-check.sh && \
    printf '#!/bin/sh\necho "Generating status report..."\ndate\nuptime\ndf -h\n' > /var/www/test-data/level1-scripts/level2-utils/level3-hooks/level4-validators/level5-reports/status.sh && \
    printf '#!/bin/sh\necho "Generating performance report..."\nps aux | head -10\n' > /var/www/test-data/level1-scripts/level2-utils/level3-hooks/level4-validators/level5-reports/perf.sh

# Level 1-5 data branch (12 files)
RUN printf '{"users":[{"id":1,"name":"Admin","role":"admin"},{"id":2,"name":"User","role":"user"}]}\n' > /var/www/test-data/level1-data/users.json && \
    dd if=/dev/zero bs=10k count=1 2>/dev/null | tr '\0' 'U' >> /var/www/test-data/level1-data/users.json && \
    printf '{"products":[{"id":1,"name":"Product A","price":99.99},{"id":2,"name":"Product B","price":149.99}]}\n' > /var/www/test-data/level1-data/products.json && \
    dd if=/dev/zero bs=10k count=1 2>/dev/null | tr '\0' 'P' >> /var/www/test-data/level1-data/products.json && \
    printf '{"orders":[{"id":1,"user_id":1,"total":299.99}]}\n' > /var/www/test-data/level1-data/orders.json && \
    printf '{"settings":{"theme":"dark","lang":"en"}}\n' > /var/www/test-data/level1-data/settings.json && \
    dd if=/dev/zero bs=100k count=1 2>/dev/null | tr '\0' 'S' > /var/www/test-data/level1-data/level2-cache/sessions.db && \
    dd if=/dev/zero bs=100k count=1 2>/dev/null | tr '\0' 'Q' > /var/www/test-data/level1-data/level2-cache/query-cache.db && \
    printf 'temp upload data\n' > /var/www/test-data/level1-data/level2-cache/level3-temp/upload-001.tmp && \
    printf 'temp upload data 2\n' > /var/www/test-data/level1-data/level2-cache/level3-temp/upload-002.tmp && \
    printf 'temp upload data 3\n' > /var/www/test-data/level1-data/level2-cache/level3-temp/upload-003.tmp && \
    printf '{"task_id":1,"status":"pending","data":"processing..."}\n' > /var/www/test-data/level1-data/level2-cache/level3-temp/level4-processing/queue-001.json && \
    printf '{"task_id":2,"status":"pending","data":"waiting..."}\n' > /var/www/test-data/level1-data/level2-cache/level3-temp/level4-processing/queue-002.json && \
    printf '{"task_id":1,"status":"completed","result":"success","timestamp":"2024-01-15T10:30:00Z"}\n' > /var/www/test-data/level1-data/level2-cache/level3-temp/level4-processing/level5-results/result-001.json && \
    printf '{"task_id":2,"status":"completed","result":"success","timestamp":"2024-01-15T11:30:00Z"}\n' > /var/www/test-data/level1-data/level2-cache/level3-temp/level4-processing/level5-results/result-002.json

# Set file permissions (T025)
# 600 permissions for secrets (4 files)
RUN chmod 600 /var/www/test-data/level1-configs/level2-services/level3-overrides/level4-secrets/api.key && \
    chmod 600 /var/www/test-data/level1-configs/level2-services/level3-overrides/level4-secrets/db.creds && \
    chmod 600 /var/www/test-data/level1-configs/level2-services/level3-overrides/level4-secrets/level5-certs/ca.crt && \
    chmod 600 /var/www/test-data/level1-configs/level2-services/level3-overrides/level4-secrets/level5-certs/server.pem

# 755 permissions for scripts (10 files)
RUN chmod 755 /var/www/test-data/level1-scripts/deploy.sh && \
    chmod 755 /var/www/test-data/level1-scripts/backup.sh && \
    chmod 755 /var/www/test-data/level1-scripts/level2-utils/db-migrate.sh && \
    chmod 755 /var/www/test-data/level1-scripts/level2-utils/cache-clear.sh && \
    chmod 755 /var/www/test-data/level1-scripts/level2-utils/level3-hooks/pre-deploy.sh && \
    chmod 755 /var/www/test-data/level1-scripts/level2-utils/level3-hooks/post-deploy.sh && \
    chmod 755 /var/www/test-data/level1-scripts/level2-utils/level3-hooks/level4-validators/syntax-check.sh && \
    chmod 755 /var/www/test-data/level1-scripts/level2-utils/level3-hooks/level4-validators/config-check.sh && \
    chmod 755 /var/www/test-data/level1-scripts/level2-utils/level3-hooks/level4-validators/level5-reports/status.sh && \
    chmod 755 /var/www/test-data/level1-scripts/level2-utils/level3-hooks/level4-validators/level5-reports/perf.sh

# 644 permissions for configs, logs, and data (all remaining files - already default)
# No action needed as 644 is typically the default for files created with echo/printf

# Set file ownership (T026)
# Set 20 files to root:root (configs branch, scripts branch)
RUN chown root:root /var/www/test-data/level1-configs/app.conf && \
    chown root:root /var/www/test-data/level1-configs/database.ini && \
    chown root:root /var/www/test-data/level1-configs/level2-services/level3-overrides/dev.env && \
    chown root:root /var/www/test-data/level1-configs/level2-services/level3-overrides/test.env && \
    chown root:root /var/www/test-data/level1-configs/level2-services/level3-overrides/level4-secrets/api.key && \
    chown root:root /var/www/test-data/level1-configs/level2-services/level3-overrides/level4-secrets/db.creds && \
    chown root:root /var/www/test-data/level1-configs/level2-services/level3-overrides/level4-secrets/level5-certs/ca.crt && \
    chown root:root /var/www/test-data/level1-configs/level2-services/level3-overrides/level4-secrets/level5-certs/server.pem && \
    chown root:root /var/www/test-data/level1-scripts/deploy.sh && \
    chown root:root /var/www/test-data/level1-scripts/backup.sh && \
    chown root:root /var/www/test-data/level1-scripts/level2-utils/level3-hooks/pre-deploy.sh && \
    chown root:root /var/www/test-data/level1-scripts/level2-utils/level3-hooks/post-deploy.sh && \
    chown root:root /var/www/test-data/level1-scripts/level2-utils/level3-hooks/level4-validators/syntax-check.sh && \
    chown root:root /var/www/test-data/level1-scripts/level2-utils/level3-hooks/level4-validators/config-check.sh && \
    chown root:root /var/www/test-data/level1-scripts/level2-utils/level3-hooks/level4-validators/level5-reports/status.sh && \
    chown root:root /var/www/test-data/level1-scripts/level2-utils/level3-hooks/level4-validators/level5-reports/perf.sh && \
    chown root:root /var/www/test-data/level1-logs/level2-archives/level3-backup/backup-01.tar && \
    chown root:root /var/www/test-data/level1-logs/level2-archives/level3-backup/level4-checksums/sha256sums.txt && \
    chown root:root /var/www/test-data/level1-data/orders.json && \
    chown root:root /var/www/test-data/level1-data/settings.json

# Set 20 files to www-data:www-data (service configs, logs, cache, data)
RUN chown www-data:www-data /var/www/test-data/level1-configs/level2-services/web.conf && \
    chown www-data:www-data /var/www/test-data/level1-configs/level2-services/api.conf && \
    chown www-data:www-data /var/www/test-data/level1-logs/app.log && \
    chown www-data:www-data /var/www/test-data/level1-logs/error.log && \
    chown www-data:www-data /var/www/test-data/level1-logs/level2-archives/2024-01.log.gz && \
    chown www-data:www-data /var/www/test-data/level1-logs/level2-archives/2024-02.log.gz && \
    chown www-data:www-data /var/www/test-data/level1-scripts/level2-utils/db-migrate.sh && \
    chown www-data:www-data /var/www/test-data/level1-scripts/level2-utils/cache-clear.sh && \
    chown www-data:www-data /var/www/test-data/level1-data/users.json && \
    chown www-data:www-data /var/www/test-data/level1-data/products.json && \
    chown www-data:www-data /var/www/test-data/level1-data/level2-cache/sessions.db && \
    chown www-data:www-data /var/www/test-data/level1-data/level2-cache/query-cache.db && \
    chown www-data:www-data /var/www/test-data/level1-data/level2-cache/level3-temp/upload-001.tmp && \
    chown www-data:www-data /var/www/test-data/level1-data/level2-cache/level3-temp/upload-002.tmp && \
    chown www-data:www-data /var/www/test-data/level1-data/level2-cache/level3-temp/upload-003.tmp && \
    chown www-data:www-data /var/www/test-data/level1-data/level2-cache/level3-temp/level4-processing/queue-001.json && \
    chown www-data:www-data /var/www/test-data/level1-data/level2-cache/level3-temp/level4-processing/queue-002.json && \
    chown www-data:www-data /var/www/test-data/level1-data/level2-cache/level3-temp/level4-processing/level5-results/result-001.json && \
    chown www-data:www-data /var/www/test-data/level1-data/level2-cache/level3-temp/level4-processing/level5-results/result-002.json && \
    chown www-data:www-data /var/www/test-data/level1-logs/level2-archives/level3-backup/level4-checksums/level5-meta/manifest.json

# Copy PHP Server Manager application
COPY server-manager.php /var/www/html/server-manager.php

# Set ownership to www-data user (matches Nginx and PHP-FPM)
RUN chown www-data:www-data /var/www/html/server-manager.php

# Set file permissions (readable by all, writable by owner)
RUN chmod 644 /var/www/html/server-manager.php

# Expose HTTP port
EXPOSE 80

# Set working directory
WORKDIR /var/www/html

# Set entrypoint to launch both services
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
