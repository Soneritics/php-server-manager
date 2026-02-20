# PHP Server Manager

A single-file PHP application providing web-based server management with a console-style interface. Upload one file to your server, set a password, and manage files and execute commands from your browser.

## Features

- 🖥️ Terminal-style console interface
- 📁 File system navigation: `ls`, `cd`, `cat`
- ✏️ File operations: `mkdir`, `rm`, `cp`, `ren`
- 🔐 Permission management: `chmod`, `chown`
- ⚡ Command execution: `/exec`
- 📥 File downloads: `/download`
- 📊 System information: `/phpinfo`, `/list`, `/clear`, `/autodestruct`
- 🔒 Password-protected access
- 💾 Session-based state persistence
- 📦 Zero dependencies — native PHP only

## Quick Start

```bash
# 1. Set your password in server-manager.php
$ADMIN_PASSWORD = 'your-secure-password-here';

# 2. Upload to your server
scp server-manager.php user@server:/var/www/html/

# 3. Open in browser
https://your-domain.com/server-manager.php
```

See the [Getting Started](docs/getting-started.md) guide for detailed instructions.

## Requirements

- PHP 5.4 or higher
- Unix/Linux operating system
- Web server (Apache, Nginx, or PHP built-in server)

## Docker (Development/Testing)

```bash
docker build -t php-server-manager .
docker run -d -p 8080:80 --name php-server-manager-test php-server-manager
# Open http://localhost:8080
```

For live code editing, mount the file:

```bash
docker run -d -p 8080:80 \
  -v ./server-manager.php:/var/www/html/server-manager.php:ro \
  --name php-server-manager-test \
  php-server-manager
```

See the [Docker Guide](docs/docker.md) for details.

## Documentation

| Guide | Description |
|-------|-------------|
| [Getting Started](docs/getting-started.md) | Installation and first steps |
| [Command Reference](docs/commands.md) | All commands with examples |
| [Configuration](docs/configuration.md) | Password, session, and settings |
| [Security](docs/security.md) | Hardening and best practices |
| [Docker](docs/docker.md) | Development container setup |
| [Troubleshooting](docs/troubleshooting.md) | Common issues and solutions |
| [Architecture](docs/architecture.md) | Technical internals |
| [Contributing](docs/contributing.md) | How to contribute |

## Security

⚠️ **This tool provides powerful server access.** Before deploying:

1. Set a strong password (16+ characters)
2. Use HTTPS
3. Restrict access by IP
4. Remove after use with `/autodestruct`

See the [Security Guide](docs/security.md) for complete recommendations.

## License

MIT License — See [LICENSE](LICENSE) for details.
