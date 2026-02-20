# Security Guide

PHP Server Manager provides powerful server access through a web browser. Follow these guidelines to minimize risk.

## Before Deployment

1. **Set a strong password** — Use at least 16 characters with mixed case, numbers, and symbols.
2. **Use HTTPS** — Always deploy behind HTTPS to encrypt password transmission.
3. **Never commit your password** — Change the password after cloning and before deploying. Add the file to `.gitignore` if needed.

## Access Restriction

### IP Whitelisting (Apache)

Create or edit `.htaccess` in the same directory as `server-manager.php`:

```apache
<Files "server-manager.php">
    Order Deny,Allow
    Deny from all
    Allow from 192.168.1.100
    Allow from 10.0.0.0/8
</Files>
```

### IP Whitelisting (Nginx)

Add to your server configuration:

```nginx
location ~ /server-manager\.php$ {
    allow 192.168.1.100;
    allow 10.0.0.0/8;
    deny all;
    fastcgi_pass unix:/var/run/php/php-fpm.sock;
    include fastcgi_params;
}
```

### VPN

For the strongest protection, only access the server manager through a VPN connection and restrict access to the VPN IP range.

## During Use

- **Monitor access logs** — Check your web server access logs for unexpected requests to `server-manager.php`.
- **Limit session duration** — The built-in 24-minute session timeout provides automatic protection. Re-authenticate when prompted.
- **Be careful with destructive commands** — `rm` deletes immediately without confirmation. There is no undo.
- **Back up before modifying** — Use `cp` to create backups before editing or deleting important files.

## After Use

**Remove the file from the server** when you're done. Use the built-in self-destruct command:

```
> /autodestruct
```

Or delete manually:

```bash
rm /var/www/html/server-manager.php
```

Leaving the file on a publicly accessible server is a security risk, even with a strong password.

## Built-in Security Features

| Feature | Description |
|---------|-------------|
| Password authentication | All access requires the configured password |
| Session-based auth | Authentication state is tied to a server-side session |
| HttpOnly cookies | Session cookies are not accessible via JavaScript |
| SameSite cookies | Prevents cross-site request forgery |
| Session timeout | Automatic logout after 24 minutes |
| Path traversal protection | `realpath()` validation prevents directory traversal attacks |
| HTML escaping | All output is escaped to prevent XSS in the console |

## Known Limitations

- The password is stored in plain text in the PHP source file.
- The `/exec` command allows arbitrary shell command execution by design.
- There is no rate limiting on login attempts.
- There is no audit log beyond standard web server access logs.
- Sessions are not encrypted at rest on the server.

## Threat Model

This tool is designed for **temporary use in trusted environments**. It is not intended as a permanent server management solution. The expected usage pattern is:

1. Upload to server
2. Perform maintenance tasks
3. Remove from server

If you need persistent server management, consider purpose-built tools with stronger security models.
