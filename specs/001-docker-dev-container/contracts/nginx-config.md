# Contract: Nginx Configuration

**Purpose**: Define the exact Nginx configuration required to serve PHP Server Manager through PHP-FPM in the development container.

**Audience**: Implementers creating the Dockerfile and nginx configuration file.

---

## Configuration File Location

**Path**: `/etc/nginx/nginx.conf` (or included file in `/etc/nginx/conf.d/`)

**Creation Method**: Inline heredoc in Dockerfile RUN command OR separate COPY command

---

## Required Configuration Blocks

### 1. Global Directives

```nginx
user www-data;
worker_processes auto;
```

**Contract Requirements**:
- ✅ `user` MUST be `www-data` to match PHP-FPM user
- ✅ `worker_processes` MUST be `auto` for dynamic CPU scaling

---

### 2. Events Block

```nginx
events {
    worker_connections 1024;
}
```

**Contract Requirements**:
- ✅ `worker_connections` MUST be at least 1024
- ✅ Block MUST be present (Nginx requirement)

---

### 3. HTTP Block

```nginx
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

**Contract Requirements**:
- ✅ `include /etc/nginx/mime.types` MUST be present for correct Content-Type headers
- ✅ `default_type application/octet-stream` MUST be set as fallback
- ✅ Server block MUST exist with following attributes:
  - **listen**: `80` (HTTP port, meets FR-006)
  - **server_name**: `_` (catch-all, accepts any hostname)
  - **root**: `/var/www/html` (document root, meets FR-005)
  - **index**: `server-manager.php` (default file)
- ✅ Location block for PHP files MUST:
  - Match pattern: `~ \.php$` (regex for .php extension)
  - Use FastCGI pass: `unix:/run/php-fpm/php-fpm.sock` (Unix socket, not TCP)
  - Set `fastcgi_index` to `server-manager.php`
  - Include `fastcgi_params` (standard FastCGI parameters)
  - Set `SCRIPT_FILENAME` to `$document_root$fastcgi_script_name`

---

## Configuration Validation

### Build-time Validation

```bash
# Inside Dockerfile, after configuration is created:
RUN nginx -t
```

**Expected Output**: `nginx: configuration file /etc/nginx/nginx.conf test is successful`

**Contract**: Configuration MUST pass `nginx -t` validation before container build completes.

---

## Integration with PHP-FPM

### Socket Path Contract

**PHP-FPM Socket**: `/run/php-fpm/php-fpm.sock`

**Contract Requirements**:
- ✅ Nginx configuration MUST reference the EXACT socket path
- ✅ Socket directory `/run/php-fpm/` MUST be created before PHP-FPM starts
- ✅ Socket MUST exist when Nginx starts (entrypoint script dependency)

### User/Group Contract

**PHP-FPM User**: `www-data`  
**Nginx User**: `www-data`

**Contract**: Both processes MUST run as same user for socket communication.

---

## Dockerfile Integration Pattern

### Option A: Inline Heredoc (Recommended)

```dockerfile
RUN cat > /etc/nginx/nginx.conf <<'EOF'
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
EOF
```

**Advantages**:
- Self-contained (meets FR-008)
- No external files required
- Easy to read in Dockerfile

### Option B: COPY Command (Alternative)

```dockerfile
COPY nginx.conf /etc/nginx/nginx.conf
```

**Advantages**:
- Cleaner Dockerfile
- Easier to edit externally

**Disadvantage**: Requires external file (conflicts with FR-008 preference for self-containment)

**Contract**: Use Option A (inline) unless external configuration is strongly justified.

---

## Security Considerations (Development Container)

**Contract**: Following security measures are OPTIONAL for development containers:

- ❌ SSL/HTTPS (out of scope per feature spec)
- ❌ Security headers (X-Frame-Options, CSP, etc.)
- ❌ Rate limiting
- ❌ Request size limits (client_max_body_size)
- ❌ Access logs (can be disabled for performance)

**Rationale**: Development container prioritizes simplicity over security (per constitution "usability over performance").

---

## Error Handling Contract

**Contract**: Configuration SHOULD include basic error handling:

```nginx
# Optional but recommended
error_log /var/log/nginx/error.log warn;
access_log off;  # Disable for performance
```

**Required**: Error output MUST be visible to help developers debug container issues.

---

## Testing Contract

### Manual Verification (Per Constitution)

After container starts, developers MUST manually verify:

1. **Container starts**: `docker run -p 8080:80 php-server-manager`
2. **HTTP responds**: `curl http://localhost:8080`
3. **PHP processes**: Browser shows PHP Server Manager interface
4. **Logs visible**: `docker logs <container-id>` shows startup messages

**Contract**: No automated tests required (per Principle IV: No Testing Required).

---

## Change Management

### Modifying the Contract

**If implementation needs differ from this contract:**

1. Document the change in `plan.md` Complexity Tracking section
2. Explain why the contract specification is insufficient
3. Update this contract file with the new specification
4. Ensure constitutional compliance

---

## Compliance Checklist

- ✅ Configuration listens on port 80 (FR-006)
- ✅ Configuration serves PHP files via FastCGI (FR-002)
- ✅ Configuration uses Unix socket (not TCP)
- ✅ Configuration sets document root to `/var/www/html` (FR-005)
- ✅ Configuration passes `nginx -t` validation
- ✅ User is `www-data` (matches PHP-FPM)
- ✅ Socket path is `/run/php-fpm/php-fpm.sock`
- ✅ No external dependencies beyond base image files
- ✅ Development-focused (no unnecessary security hardening)

---

## Implementation Reference

**Next Steps**:
1. Implement configuration in Dockerfile using Option A (inline heredoc)
2. Validate with `nginx -t` during build
3. Test manually after container starts
4. Document any deviations in plan.md
