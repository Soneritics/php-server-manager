# Troubleshooting

## "Unable to start session"

**Cause:** PHP session support is not enabled or the temp directory is not writable.

**Solution:**

1. Verify PHP session support:
   ```bash
   php -i | grep session
   ```
2. Check temp directory permissions:
   ```bash
   ls -la /tmp
   ```
3. Check the PHP error log for detailed messages.

## "Permission denied" Errors

**Cause:** The web server user does not have permission to access the target file or directory.

**Solution:**

1. Check the current user:
   ```
   > /exec whoami
   ```
2. Check file permissions:
   ```
   > ls
   ```
3. Adjust permissions if needed:
   ```
   > chmod 644 file.txt
   ```

The application runs as the web server user (`www-data`, `apache`, or `nginx` depending on your setup). It can only access files that this user has permission to read or write.

## Commands Not Executing (`/exec`)

**Cause:** The `exec()` function is disabled in PHP configuration.

**Solution:**

1. Check disabled functions:
   ```
   > /exec php -i | grep disable_functions
   ```
2. If `exec` is listed, contact your hosting provider or edit `php.ini` to remove it from `disable_functions`.
3. Built-in file commands (`ls`, `cd`, `cat`, etc.) work without `exec()`.

## Session Expires Too Quickly

**Cause:** The session timeout is set to 24 minutes by design.

**Solution:**

Re-authenticate when prompted. To change the timeout, modify the `1440` value (seconds) in `server-manager.php`:

```php
if (time() - $_SESSION['login_time'] > 1440) {
```

## Console Output Lost After Refresh

**Cause:** The session expired or the browser did not send the session cookie.

**Solution:**

1. Verify cookies are enabled in your browser.
2. Re-authenticate with your password.
3. Check that PHP session files are being created:
   ```bash
   ls /var/lib/php/sessions
   ```

## Blank Page After Login

**Cause:** PHP errors are being suppressed.

**Solution:**

1. Check the PHP error log:
   ```bash
   tail -50 /var/log/php_errors.log
   ```
2. Temporarily enable error display by adding to the top of `server-manager.php`:
   ```php
   ini_set('display_errors', 1);
   error_reporting(E_ALL);
   ```

## `/download` Not Working

**Cause:** The file does not exist, is not readable, or headers have already been sent.

**Solution:**

1. Verify the file exists:
   ```
   > ls
   ```
2. Check file permissions:
   ```
   > chmod 644 target-file.txt
   ```
3. Ensure there are no PHP errors or whitespace before `<?php` in the file.

## `/phpinfo` Shows Error

**Cause:** The `phpinfo()` function may be disabled by your hosting provider.

**Solution:**

Check disabled functions:

```
> /exec php -r "phpinfo();"
```

If disabled, contact your hosting provider.

## Docker-Specific Issues

See the [Docker Guide](docker.md#troubleshooting) for container-related problems.
