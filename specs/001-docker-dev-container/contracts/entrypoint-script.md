# Contract: Entrypoint Script

**Purpose**: Define the exact shell script required to start and manage both Nginx and PHP-FPM processes in the development container.

**Audience**: Implementers creating the Dockerfile and entrypoint script.

---

## Script File Location

**Path**: `/usr/local/bin/docker-entrypoint.sh` (or `/entrypoint.sh`)

**Creation Method**: Inline heredoc in Dockerfile RUN command OR separate COPY command

**Permissions**: `755` (executable)

---

## Required Script Structure

### Complete Script Template

```bash
#!/bin/bash
set -e

# Start PHP-FPM in background
php-fpm --nodaemonize &

# Start Nginx in foreground (becomes PID 1)
exec nginx -g "daemon off;"
```

---

## Contract Requirements

### 1. Shebang Line

```bash
#!/bin/bash
```

**Contract**:
- ✅ MUST use `#!/bin/bash` (Bash interpreter)
- ✅ MUST be first line of script
- ❌ Do NOT use `/bin/sh` (may lack features in minimal containers)

---

### 2. Error Handling

```bash
set -e
```

**Contract**:
- ✅ MUST include `set -e` to exit on any command failure
- ✅ Ensures container fails fast if PHP-FPM or Nginx won't start

---

### 3. PHP-FPM Startup

```bash
php-fpm --nodaemonize &
```

**Contract**:
- ✅ MUST use `php-fpm` command (available in php:fpm-alpine)
- ✅ MUST include `--nodaemonize` flag (prevents process from backgrounding and exiting)
- ✅ MUST end with `&` (runs in background)
- ✅ MUST start BEFORE Nginx (ensures socket exists when Nginx starts)

**Rationale**: `--nodaemonize` keeps PHP-FPM running as a foreground process in its own subshell, preventing premature exit.

---

### 4. Nginx Startup

```bash
exec nginx -g "daemon off;"
```

**Contract**:
- ✅ MUST use `exec` (replaces shell process with Nginx, making Nginx PID 1)
- ✅ MUST include `-g "daemon off;"` flag (prevents Nginx from backgrounding)
- ✅ MUST be LAST command in script
- ✅ MUST start AFTER PHP-FPM (depends on socket availability)

**Rationale**: 
- `exec` makes Nginx PID 1, enabling proper signal handling (SIGTERM from `docker stop`)
- `daemon off;` keeps Nginx in foreground, preventing container exit

---

## Process Lifecycle Contract

### Startup Sequence

```
1. Container starts
   ↓
2. Entrypoint script executes (shell PID 1)
   ↓
3. PHP-FPM starts in background
   ↓ (creates /run/php-fpm/php-fpm.sock)
4. Nginx starts in foreground via exec (becomes PID 1)
   ↓ (connects to PHP-FPM socket)
5. Container running (HTTP listening on port 80)
```

**Contract**: Each step MUST succeed before next step executes (enforced by `set -e`).

---

### Shutdown Sequence

```
1. docker stop (sends SIGTERM to PID 1 = Nginx)
   ↓
2. Nginx receives SIGTERM, begins graceful shutdown
   ↓
3. Nginx finishes active requests, closes connections
   ↓
4. Nginx exits (exit code 0)
   ↓
5. Container stops
   ↓ (PHP-FPM killed by Docker after Nginx exits)
```

**Contract**: 
- ✅ Nginx MUST handle SIGTERM gracefully
- ✅ Container MUST stop within Docker's grace period (default 10 seconds)

---

## Socket Dependency Contract

### PHP-FPM Socket Creation

**Expected Socket**: `/run/php-fpm/php-fpm.sock`

**Contract**:
- ✅ Socket directory `/run/php-fpm/` MUST exist before PHP-FPM starts
- ✅ PHP-FPM MUST create socket within 1-2 seconds of starting
- ✅ Socket MUST be accessible to Nginx user (`www-data`)

### Ensuring Socket Exists

**Optional Enhancement** (not required for basic functionality):

```bash
#!/bin/bash
set -e

# Ensure socket directory exists
mkdir -p /run/php-fpm

# Start PHP-FPM in background
php-fpm --nodaemonize &

# Wait briefly for socket creation
sleep 1

# Verify socket exists (optional - helps debugging)
if [ ! -S /run/php-fpm/php-fpm.sock ]; then
    echo "Error: PHP-FPM socket not created"
    exit 1
fi

# Start Nginx in foreground
exec nginx -g "daemon off;"
```

**Contract**: Socket verification is OPTIONAL but RECOMMENDED for better error messages.

---

## Dockerfile Integration Pattern

### Option A: Inline Heredoc (Recommended)

```dockerfile
RUN cat > /usr/local/bin/docker-entrypoint.sh <<'EOF'
#!/bin/bash
set -e

php-fpm --nodaemonize &
exec nginx -g "daemon off;"
EOF

RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
```

**Advantages**:
- Self-contained (meets FR-008)
- No external files required
- Easy to review in Dockerfile

### Option B: COPY Command (Alternative)

```dockerfile
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
```

**Advantages**:
- Cleaner Dockerfile
- Easier to edit externally

**Disadvantage**: Requires external file (conflicts with FR-008 preference)

**Contract**: Use Option A (inline) unless external script is strongly justified.

---

## Executable Permissions Contract

**Contract**:
- ✅ Script MUST have executable permissions (chmod +x)
- ✅ Permissions MUST be set in Dockerfile (not runtime)
- ✅ Permissions MUST be `755` or `700` minimum

**Validation**:
```bash
# In Dockerfile
RUN chmod +x /usr/local/bin/docker-entrypoint.sh
```

---

## ENTRYPOINT Directive Contract

**Contract**:
```dockerfile
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
```

**Requirements**:
- ✅ MUST use exec form `["command", "arg1", ...]` (not shell form)
- ✅ MUST reference absolute path to script
- ✅ MUST be last ENTRYPOINT directive in Dockerfile

**Exec form vs Shell form**:
- ✅ Exec form: `["command"]` - direct execution, proper signal handling
- ❌ Shell form: `command` - wrapped in /bin/sh -c, may interfere with signals

---

## Error Handling Contract

### Expected Errors

**PHP-FPM won't start**:
```
Error output: php-fpm: unable to bind listening socket
Exit code: 1
```

**Contract**: Container MUST exit immediately (enforced by `set -e`)

**Nginx won't start**:
```
Error output: nginx: [emerg] bind() to 0.0.0.0:80 failed
Exit code: 1
```

**Contract**: Container MUST exit immediately (enforced by `set -e`)

---

## Logging Contract

**Contract**:
- ✅ All script output MUST go to stdout/stderr (captured by `docker logs`)
- ✅ PHP-FPM errors MUST be visible in container logs
- ✅ Nginx errors MUST be visible in container logs

**Optional Enhancements**:
```bash
echo "Starting PHP-FPM..."
php-fpm --nodaemonize &

echo "Starting Nginx..."
exec nginx -g "daemon off;"
```

**Contract**: Logging statements are OPTIONAL but RECOMMENDED for debugging.

---

## Testing Contract

### Manual Verification (Per Constitution)

After implementation, developers MUST manually verify:

1. **Container starts successfully**: `docker run -p 8080:80 php-server-manager`
2. **Both processes running**: `docker exec <container-id> ps aux | grep -E "php-fpm|nginx"`
3. **HTTP responds**: `curl http://localhost:8080`
4. **Clean shutdown**: `docker stop <container-id>` exits within 10 seconds
5. **Restart works**: `docker restart <container-id>` succeeds

**Contract**: No automated tests required (per Principle IV: No Testing Required).

---

## Signal Handling Contract

### Required Signals

**SIGTERM** (docker stop):
- ✅ Nginx MUST receive SIGTERM (via exec replacing shell)
- ✅ Nginx MUST gracefully shut down active connections
- ✅ Container MUST exit with code 0 after graceful shutdown

**SIGINT** (Ctrl+C):
- ✅ Same behavior as SIGTERM

**Contract**: Signal handling is AUTOMATIC via `exec` command (no custom handlers needed).

---

## Performance Contract

**Startup Time**:
- ✅ Script execution MUST complete within 5 seconds
- ✅ Container MUST be HTTP-ready within 10 seconds (SC-002)

**Resource Usage**:
- ✅ Script itself MUST not consume significant resources (minimal bash overhead)
- ✅ No loops, polling, or complex logic in script

---

## Compliance Checklist

- ✅ Uses `#!/bin/bash` shebang
- ✅ Includes `set -e` for error handling
- ✅ Starts PHP-FPM with `--nodaemonize &`
- ✅ Starts Nginx with `exec nginx -g "daemon off;"`
- ✅ PHP-FPM starts BEFORE Nginx
- ✅ Nginx becomes PID 1 (via exec)
- ✅ Script is executable (chmod +x)
- ✅ ENTRYPOINT uses exec form
- ✅ No external dependencies (bash is built-in)
- ✅ Script keeps container alive (FR-003)
- ✅ Script launches both processes (FR-003)

---

## Implementation Reference

**Next Steps**:
1. Implement script in Dockerfile using Option A (inline heredoc)
2. Set executable permissions with `chmod +x`
3. Configure ENTRYPOINT directive using exec form
4. Test manually with docker run/stop/restart
5. Document any deviations in plan.md
