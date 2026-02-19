# PHP Server Manager

A single-file PHP application providing web-based server management with a console-style interface.

## Features

- 🖥️ Terminal-style console interface (black background, green text, monospace font)
- 📁 File system navigation: `ls`, `cd`, `cat`
- ✏️ File operations: `mkdir`, `rm`, `cp`, `ren`
- 🔐 Permission management: `chmod`, `chown`
- ⚡ Command execution: `/exec`
- 📥 File downloads: `/download`
- 📊 System information: `/phpinfo`, `/list`, `/clear`, `/autodestruct`
- 🔒 Password-protected access
- 💾 Session-based state persistence

## Quick Start

### 1. Set Your Password

Edit `server-manager.php` and change the password variable:

```php
$ADMIN_PASSWORD = 'your-secure-password-here';
```

### 2. Upload to Server

Upload `server-manager.php` to your web server via FTP, SCP, or file manager.

Example locations:
- `/var/www/html/server-manager.php`
- `/home/username/public_html/tools/server-manager.php`

### 3. Access in Browser

Navigate to the file in your browser:

```
http://your-domain.com/server-manager.php
```

### 4. Login

Enter your password and start managing your server!

## Commands

### File System Navigation

```bash
ls              # List directory contents
cd <path>       # Change directory
cat <file>      # View file contents
```

### File Operations

```bash
mkdir <dir>     # Create directory
rm <path>       # Remove file or directory
cp <src> <dst>  # Copy file
ren <old> <new> # Rename file or directory
```

### Permission Management

```bash
chmod <mode> <path>        # Change permissions (e.g., chmod 755 file.txt)
chown <user:group> <path>  # Change ownership
```

### Special Commands

```bash
/exec <command>   # Execute arbitrary shell command
/download <file>  # Download file to browser
/phpinfo          # Display PHP configuration
/list             # List all available commands
/clear            # Clear console output
/autodestruct     # Delete this script from the server
```

## Requirements

### Server Requirements

- ✅ PHP 5.4 or higher
- ✅ Unix/Linux operating system
- ✅ Web server (Apache, Nginx, or PHP built-in server)
- ✅ PHP session support enabled
- ✅ Writable temp directory (`/tmp`)

### Optional

- HTTPS enabled (recommended for secure password transmission)
- PHP `exec()` function enabled (for `/exec` command)

## Security

⚠️ **Important Security Notes**:

1. **Strong Password**: Use a complex password before deployment (minimum 16 characters recommended)
2. **HTTPS Only**: Always use HTTPS to encrypt password transmission
3. **Remove After Use**: Delete the file when maintenance is complete using `/autodestruct` command
4. **Restrict Access**: Use IP whitelisting or `.htaccess` restrictions
5. **Trusted Environment**: Only use in environments you control
6. **Don't Commit Password**: Never commit the file with your password to version control
7. **Monitor Access**: Check web server logs regularly for unauthorized access attempts
8. **Backup Important Files**: Before using destructive commands like `rm`, ensure you have backups

### Example .htaccess (Apache)

```apache
Order Deny,Allow
Deny from all
Allow from 192.168.1.100  # Your IP only
Allow from 10.0.0.0/8     # Your VPN network
```

### Example Nginx Configuration

```nginx
location ~ /server-manager\.php$ {
    allow 192.168.1.100;  # Your IP only
    deny all;
    fastcgi_pass unix:/var/run/php/php-fpm.sock;
    include fastcgi_params;
}
```

## Permissions

The application operates with the permissions of the web server user:
- **Apache**: typically `www-data` or `apache`
- **Nginx**: typically `www-data` or `nginx`
- **PHP built-in**: your system user

Check your user with: `/exec whoami`

## Development

### Using PHP Built-in Server

```bash
cd /path/to/directory
php -S localhost:8000 server-manager.php
```

Access at: `http://localhost:8000`

## Troubleshooting

### "Unable to start session"

**Solution**: Check PHP session support and temp directory permissions.

```bash
php -i | grep session
ls -la /tmp
```

### "Permission denied" errors

**Solution**: Check file permissions and web server user.

```bash
# Check current user
/exec whoami

# Check file permissions
ls -la
```

### Commands not executing

**Solution**: Check if `exec()` is disabled in PHP configuration.

```bash
/exec php -i | grep disable_functions
```

## License

MIT License - See LICENSE file for details

## Support

- View all commands: Type `/list` in the console
- Check PHP configuration: Type `/phpinfo` in the console
- Review specification: See `specs/001-php-server-manager/spec.md`

## Version

**Current Version**: 1.0.0

---

**Created**: 2025-01-24  
**Platform**: Unix/Linux only  
**Dependencies**: None (native PHP only)
