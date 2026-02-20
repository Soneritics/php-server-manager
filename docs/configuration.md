# Configuration

PHP Server Manager is configured by editing variables directly in `server-manager.php`.

## Password

The only required configuration is setting the admin password:

```php
// ===== CONFIGURATION: SET YOUR PASSWORD HERE =====
$ADMIN_PASSWORD = 'your-secure-password-here';
// =================================================
```

**Guidelines:**

- Minimum 16 characters recommended
- Use a mix of uppercase, lowercase, numbers, and symbols
- Never commit the file with your actual password to version control
- Change the password before every deployment

## Session Timeout

The session automatically expires after **24 minutes** (1440 seconds) of inactivity. This is hardcoded in the application:

```php
if (time() - $_SESSION['login_time'] > 1440) {
    session_destroy();
    // ...
}
```

To change the timeout, modify the value `1440` in `server-manager.php`. The value is in seconds.

## Session Security

Sessions are configured with security-hardened settings:

| Setting | Value | Description |
|---------|-------|-------------|
| `cookie_httponly` | `true` | Prevents JavaScript access to session cookies |
| `cookie_samesite` | `Strict` | Prevents cross-site request forgery |

## Console Output Storage

Console output is stored in temporary files at:

```
{sys_temp_dir}/console_output_{session_id}.txt
```

Typically this resolves to `/tmp/console_output_*.txt`. These files are cleaned up when the session expires or when `/clear` is used.

## Permissions Context

The application runs with the permissions of the web server process:

| Web Server | Typical User |
|------------|-------------|
| Apache | `www-data` or `apache` |
| Nginx + PHP-FPM | `www-data` or `nginx` |
| PHP built-in server | Your system user |

To check the current user, run:

```
> /exec whoami
```

All file operations (read, write, delete, permission changes) are limited to what this user is allowed to do on the system.
