# Quickstart Guide: Docker Development Container

**Feature**: 001-docker-dev-container  
**Audience**: Developers testing PHP Server Manager  
**Time to First Run**: ~5 minutes

---

## Prerequisites

✅ Docker installed on your machine ([Get Docker](https://docs.docker.com/get-docker/))  
✅ Basic familiarity with command line  
✅ Port 8080 available on your machine (or choose different port)

**Verify Docker is working**:
```bash
docker --version
```

Expected output: `Docker version 20.10.0` or higher

---

## Quick Start (3 Steps)

### Step 1: Build the Container

```bash
# Navigate to project root
cd /path/to/php-server-manager

# Build the Docker image
docker build -t php-server-manager .
```

**Expected Output**:
```
[+] Building 120.5s (12/12) FINISHED
 => [1/8] FROM docker.io/library/php:8.2-fpm-alpine
 => [2/8] RUN apk add --no-cache nginx
 => [3/8] RUN mkdir -p /var/www/test-data...
 => [8/8] WORKDIR /var/www/html
 => exporting to image
 => => naming to docker.io/library/php-server-manager
```

**Build time**: ~2-3 minutes (first time), ~30 seconds (subsequent builds)

---

### Step 2: Run the Container

```bash
# Start the container with port mapping
docker run -d -p 8080:80 --name php-server-manager-test php-server-manager
```

**Flags explained**:
- `-d` = Run in background (detached mode)
- `-p 8080:80` = Map host port 8080 to container port 80
- `--name php-server-manager-test` = Friendly container name

**Expected Output**:
```
a1b2c3d4e5f6... (container ID)
```

**Verify container is running**:
```bash
docker ps
```

Expected: `STATUS` shows "Up X seconds"

---

### Step 3: Access in Browser

Open your browser and navigate to:
```
http://localhost:8080
```

**Expected**: PHP Server Manager interface loads with login screen.

**Default credentials** (change before production):
- Password: `change-this-password-before-deployment`

---

## Testing with Pre-populated Data

After logging in, you'll see pre-populated test data:

### Test Directory Structure
- **5 levels deep**: `/var/www/test-data/level1/level2/level3/level4/level5`
- **40+ files**: Configuration files, logs, scripts, data files
- **Various file types**: `.conf`, `.ini`, `.log`, `.sh`, `.json`, `.db`
- **Different permissions**: 600 (secrets), 644 (configs), 755 (scripts)
- **Different owners**: `root:root` and `www-data:www-data`

### Browse Test Data
1. Navigate to `/var/www/test-data` in the PHP Server Manager
2. Click through directory levels to explore nested structure
3. View files of different types and sizes
4. Test file operations (ls, cat, download, etc.)

---

## Development Workflow (Iterative Testing)

### Mount Local Code for Live Editing

Stop the container if running:
```bash
docker stop php-server-manager-test
docker rm php-server-manager-test
```

Restart with volume mount:
```bash
docker run -d -p 8080:80 \
  -v ./server-manager.php:/var/www/html/server-manager.php:ro \
  --name php-server-manager-test \
  php-server-manager
```

**Benefits**:
- ✅ Edit `server-manager.php` on your local machine
- ✅ Changes visible immediately (refresh browser)
- ✅ No container rebuild needed

**Workflow**:
1. Edit `server-manager.php` in your favorite editor
2. Save the file
3. Refresh browser (Ctrl+R or Cmd+R)
4. Changes appear instantly

**Important**: Mount is **read-only** (`:ro`) for safety. Container cannot modify your local file.

---

## Container Management

### View Container Logs
```bash
docker logs php-server-manager-test
```

Expected output:
```
Starting PHP-FPM...
Starting Nginx...
[notice] 1#1: nginx/1.24.0
[notice] 1#1: start worker processes
```

### Stop the Container
```bash
docker stop php-server-manager-test
```

### Start Stopped Container
```bash
docker start php-server-manager-test
```

### Restart Container
```bash
docker restart php-server-manager-test
```

### Remove Container
```bash
docker rm -f php-server-manager-test
```

**Note**: Test data is recreated each time you run a new container from the image.

---

## Troubleshooting

### Port Already in Use

**Error**: `Bind for 0.0.0.0:8080 failed: port is already allocated`

**Solution**: Use different port
```bash
docker run -d -p 9090:80 --name php-server-manager-test php-server-manager
```

Then access: `http://localhost:9090`

---

### Container Exits Immediately

**Check logs**:
```bash
docker logs php-server-manager-test
```

**Common causes**:
- PHP-FPM failed to start (check error message)
- Nginx configuration error (check `nginx: [emerg]` messages)
- Port 80 inside container already in use

---

### Cannot Access in Browser

**Verify container is running**:
```bash
docker ps
```

**Test with curl**:
```bash
curl http://localhost:8080
```

Expected: HTML output from PHP Server Manager

**Check port mapping**:
```bash
docker port php-server-manager-test
```

Expected: `80/tcp -> 0.0.0.0:8080`

---

### Volume Mount Not Working

**Verify file exists**:
```bash
ls -la ./server-manager.php
```

**Check mount inside container**:
```bash
docker exec php-server-manager-test ls -la /var/www/html/
```

Expected: `server-manager.php` should be present

**Verify changes are reflected**:
1. Add comment to top of `server-manager.php`: `// Test change`
2. Refresh browser
3. View page source (should show comment)

---

### Permission Denied Errors

**Symptom**: PHP errors about reading/writing files

**Check PHP-FPM user**:
```bash
docker exec php-server-manager-test ps aux | grep php-fpm
```

Expected: Processes running as `www-data`

**Check file permissions**:
```bash
docker exec php-server-manager-test ls -la /var/www/html/
```

Expected: `server-manager.php` readable by www-data

---

## Advanced Usage

### Interactive Shell Access

```bash
# Start bash inside running container
docker exec -it php-server-manager-test /bin/sh
```

**Useful for**:
- Inspecting file permissions
- Testing PHP commands
- Debugging configuration issues

**Example commands inside container**:
```bash
# Check Nginx status
ps aux | grep nginx

# Check PHP-FPM status
ps aux | grep php-fpm

# Test PHP
php -v

# View Nginx config
cat /etc/nginx/nginx.conf

# Browse test data
ls -laR /var/www/test-data
```

Exit shell: Type `exit` and press Enter

---

### View Test Data Structure

```bash
# From host machine
docker exec php-server-manager-test find /var/www/test-data -type f
```

Shows all test files and their paths.

---

### Check Resource Usage

```bash
docker stats php-server-manager-test
```

Shows CPU, memory, network usage in real-time. Press Ctrl+C to exit.

---

## Next Steps

### For Testing PHP Server Manager
1. ✅ Container running and accessible
2. Navigate through test data hierarchy
3. Test file operations (browse, view, download)
4. Test permission scenarios (root vs www-data files)
5. Test with different file sizes (1KB, 100KB, 1MB)

### For Development
1. ✅ Volume mount configured
2. Make code changes locally
3. Test changes immediately in browser
4. Iterate quickly without rebuilding

### For Production Deployment
❌ **This container is NOT production-ready**:
- No SSL/HTTPS
- No security hardening
- No performance optimization
- Default password exposed

**DO NOT** deploy this container to production environments.

---

## Build Time Expectations

| Operation | First Time | Subsequent |
|-----------|------------|------------|
| docker build | ~2-3 minutes | ~30 seconds |
| docker run | ~5-10 seconds | ~5-10 seconds |
| HTTP ready | ~5-10 seconds | ~5-10 seconds |
| Total (fresh start) | **~3-4 minutes** | **~1 minute** |

**Success Criteria Met**:
- ✅ SC-001: Build and start in under 5 minutes ✓
- ✅ SC-002: Accessible within 10 seconds of start ✓
- ✅ SC-005: Code changes reflected within 2 seconds ✓
- ✅ SC-006: No more than 2 simple commands (build + run) ✓

---

## Support

### Getting Help

**Check logs first**:
```bash
docker logs php-server-manager-test
```

**Common issues**:
- Port conflicts → Use different port with `-p 9090:80`
- File not found → Verify Dockerfile copied server-manager.php
- Permission denied → Check file ownership and permissions
- Connection refused → Verify container is running with `docker ps`

---

## Summary

**To run PHP Server Manager in Docker**:

```bash
# 1. Build once
docker build -t php-server-manager .

# 2. Run (testing)
docker run -d -p 8080:80 --name php-server-manager-test php-server-manager

# 3. Access
open http://localhost:8080

# OR: Run (development with live code editing)
docker run -d -p 8080:80 \
  -v ./server-manager.php:/var/www/html/server-manager.php:ro \
  --name php-server-manager-test \
  php-server-manager
```

**That's it!** 🚀

You now have a fully isolated, safe testing environment with realistic test data, ready for exploring PHP Server Manager features.
