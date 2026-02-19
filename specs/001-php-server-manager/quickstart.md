# Quickstart Guide: PHP Server Manager

**Version**: 1.0.0  
**Target**: Server administrators needing web-based file management  
**Time to Deploy**: ~5 minutes

---

## What Is This?

PHP Server Manager is a single-file web application that provides a terminal-style interface for managing a Unix/Linux server through your web browser. Upload one PHP file to your server, set a password, and immediately start managing files and executing commands.

**Key Features**:
- ✅ Single PHP file - no dependencies, no installation
- ✅ Terminal-style console interface
- ✅ File system navigation (ls, cd, cat, mkdir, rm, cp, ren)
- ✅ Permission management (chmod, chown)
- ✅ Command execution (/exec)
- ✅ File downloads (/download)
- ✅ Password protected access
- ✅ Session-based state management

---

## Quick Deploy (5 Minutes)

### Step 1: Get the File

Download `server-manager.php` from the repository or copy it to your local machine.

### Step 2: Set Your Password

Open `server-manager.php` in a text editor and modify the password variable at the top:

```php
<?php
// ===== CONFIGURATION: SET YOUR PASSWORD HERE =====
$ADMIN_PASSWORD = 'your-secure-password-here';  // ← Change this!
// =================================================
```

**Security Note**: Choose a strong password. This file provides powerful server access!

### Step 3: Upload to Server

Upload `server-manager.php` to your web server using FTP, SCP, or your hosting panel's file manager.

**Example locations**:
- `/var/www/html/server-manager.php`
- `/home/username/public_html/tools/server-manager.php`
- Any directory accessible via HTTP

### Step 4: Access in Browser

Navigate to the file in your web browser:

```
http://your-domain.com/server-manager.php
```

**Examples**:
- `http://example.com/server-manager.php`
- `https://myserver.com/tools/server-manager.php`

### Step 5: Login

Enter the password you set in Step 2 and press Enter.

### Step 6: Start Managing!

You're in! Type commands in the console interface:

```
> ls
> cd subdir
> cat config.php
> mkdir backup
```

---

## First Commands to Try

### List Current Directory

```
> ls
```

Shows all files and directories with permissions, owner, size, and modification date.

### Navigate to a Directory

```
> cd /var/www/html
```

Change to an absolute path, or use relative paths:

```
> cd subdir      # Go into subdirectory
> cd ..          # Go up one level
```

### View a File

```
> cat index.php
```

Display the contents of a text file in the console.

### Create a Directory

```
> mkdir new-folder
```

### Download a File

```
> /download config.php
```

Browser will prompt you to save the file locally.

### See All Commands

```
> /list
```

Displays complete list of available commands with syntax.

---

## Common Tasks

### Task: Backup a File

```
> cp important.txt important.txt.backup
```

### Task: Change File Permissions

```
> chmod 644 config.php
```

Makes file readable by owner and group, writable by owner only.

### Task: View PHP Configuration

```
> /phpinfo
```

Opens PHP configuration page (same as phpinfo() function).

### Task: Execute Custom Command

```
> /exec whoami
```

Execute any shell command. Examples:
- `/exec df -h` - Check disk space
- `/exec ps aux` - List processes
- `/exec tail -f /var/log/apache2/access.log` - View log

### Task: Clear Console

```
> /clear
```

Clears all previous output, starts fresh.

---

## Requirements

### Server Requirements

✅ **PHP 5.4 or higher**  
✅ **Unix/Linux operating system** (Ubuntu, Debian, CentOS, etc.)  
✅ **Web server** (Apache, Nginx, or PHP built-in server)  
✅ **PHP session support** (enabled by default)  
✅ **Writable temp directory** (`/tmp` typically)

**Optional but Recommended**:
- HTTPS enabled (for secure password transmission)
- PHP `exec()` function enabled (for /exec command)
- File system read/write permissions for web server user

### Browser Requirements

✅ Modern web browser (Chrome, Firefox, Safari, Edge)  
✅ JavaScript enabled (for Enter key form submission)  
✅ Cookies enabled (for session management)

---

## Understanding the Interface

### Console Output Area

The black screen shows:
1. **Header**: "PHP Server Manager 1.0.0" (always present)
2. **Command history**: All previous commands and their output
3. **Current directory**: Shown at the bottom

### Input Field

Bottom of screen shows:
```
> [Type your command here]
```

- Type command and press **Enter** to execute
- Page refreshes and shows result
- History persists across refreshes

### Command Types

**File Operations** (no prefix):
- `ls`, `cd`, `cat`, `mkdir`, `rm`, `cp`, `ren`, `chmod`, `chown`

**Special Commands** (/ prefix):
- `/exec`, `/download`, `/phpinfo`, `/list`, `/clear`

---

## Permissions and Security

### What Can You Do?

**You can**:
- Access any files/directories the web server user can access
- Execute any commands the web server user can run
- Modify permissions on files owned by the web server user
- Download any readable files

**You cannot**:
- Access files without read permissions
- Modify files without write permissions
- Execute commands requiring root (unless running as root - not recommended)
- Change ownership unless web server runs as root (very rare)

### Web Server User

The application runs as the web server's user account:
- **Apache**: typically `www-data` or `apache`
- **Nginx**: typically `www-data` or `nginx`
- **PHP built-in**: your system user

To see who you are:
```
> /exec whoami
```

### Security Best Practices

1. **Strong Password**: Use a complex password in the configuration
2. **HTTPS Only**: Always use HTTPS to encrypt password transmission
3. **Remove After Use**: Delete the file when maintenance is complete
4. **Restrict Access**: Use `.htaccess` or server config to IP whitelist
5. **Monitor Usage**: Check web server logs for access attempts
6. **Don't Commit**: Never commit the file with your password to version control

**Example .htaccess** (Apache):
```apache
Order Deny,Allow
Deny from all
Allow from 192.168.1.100  # Your IP only
```

---

## Troubleshooting

### Problem: "Unable to start session"

**Cause**: PHP session support not enabled or temp directory not writable

**Solution**:
1. Check PHP configuration: `php -i | grep session`
2. Verify temp directory writable: `ls -la /tmp`
3. Check PHP error log for details

### Problem: "Permission denied" errors

**Cause**: Web server user lacks permissions to access files/directories

**Solution**:
1. Check current user: `/exec whoami`
2. Check file permissions: `ls -la`
3. Adjust permissions if needed: `chmod` or contact server admin

### Problem: Commands not executing

**Cause**: `exec()` function disabled in PHP configuration

**Solution**:
1. Check if disabled: `/exec php -i | grep disable_functions`
2. Contact hosting provider to enable `exec()` if needed
3. Fallback: Use only built-in file commands (ls, cd, cat, etc.)

### Problem: Session expires too quickly

**Cause**: PHP default session timeout (24 minutes)

**Solution**:
Session timeout is automatic cleanup mechanism (by design). Re-authenticate when needed. To extend timeout, modify `session.gc_maxlifetime` in `php.ini` (requires server access).

### Problem: Console output lost after refresh

**Cause**: Session expired or browser didn't send session cookie

**Solution**:
1. Check browser cookies are enabled
2. Re-authenticate with password
3. Verify PHP session files are being created: `ls /var/lib/php/sessions`

---

## Advanced Usage

### Running as PHP Built-in Server (Development)

If you don't have Apache/Nginx, use PHP's built-in server:

```bash
cd /path/to/directory
php -S localhost:8000 server-manager.php
```

Access at: `http://localhost:8000`

### Chaining Commands (via /exec)

```
> /exec cd /var/www && ls -la && pwd
```

Execute multiple commands in sequence using `&&` or `;`.

### Recursive Directory Removal

```
> rm some-directory
```

Automatically removes directory and all contents (no confirmation prompt).

### Downloading Large Files

The `/download` command streams files directly, so size is only limited by PHP's memory and execution time settings.

---

## FAQ

### Is this secure?

It's as secure as you make it:
- ✅ Password protected
- ✅ Session-based authentication
- ✅ No command injection vulnerabilities in built-in commands
- ⚠️ `/exec` allows arbitrary commands (by design)
- ⚠️ Password stored in plain text in source
- ⚠️ Only as secure as your password

**Recommendation**: Use only in trusted environments, behind VPN, with HTTPS, and IP restrictions.

### Can I customize the interface?

Yes! Edit the inline CSS and HTML in `server-manager.php`. All code is in one file.

### What if I forget my password?

Edit `server-manager.php` and change the `$ADMIN_PASSWORD` variable. Re-upload to server.

### Does it work on Windows servers?

No. The application is designed for Unix/Linux systems only. Many commands rely on Unix file system structure and permissions.

### Can multiple users use it simultaneously?

Yes. Each user gets their own PHP session with isolated console output and directory state.

### Does it support file uploads?

No. File upload from client to server is explicitly out of scope. Use FTP/SCP for uploads.

### Can I edit files with it?

No. File editing is out of scope. Use `cat` to view, `/download` to download, edit locally, then re-upload via FTP/SCP.

---

## Command Reference Quick Sheet

| Command | Syntax | Example |
|---------|--------|---------|
| List files | `ls [path]` | `ls /var/www` |
| Change dir | `cd <path>` | `cd ..` |
| View file | `cat <file>` | `cat index.php` |
| Create dir | `mkdir <dir>` | `mkdir backups` |
| Remove | `rm <path>` | `rm old-file.txt` |
| Copy | `cp <src> <dst>` | `cp file.txt file.bak` |
| Rename | `ren <old> <new>` | `ren old.txt new.txt` |
| Permissions | `chmod <mode> <path>` | `chmod 755 script.sh` |
| Ownership | `chown <user:grp> <path>` | `chown www-data:www-data file.txt` |
| Execute | `/exec <command>` | `/exec whoami` |
| Download | `/download <file>` | `/download log.txt` |
| PHP info | `/phpinfo` | `/phpinfo` |
| Help | `/list` | `/list` |
| Clear | `/clear` | `/clear` |

---

## Next Steps

1. ✅ Deployed and logged in? Try exploring your server with `ls` and `cd`
2. 📚 Read the [Feature Specification](spec.md) for complete requirements
3. 🔧 Review [Data Model](data-model.md) to understand internal structure
4. 📋 Check [Command Interface Contract](contracts/command-interface.md) for technical details
5. 🚀 Start implementing with [tasks.md](tasks.md) (generated by /speckit.tasks)

---

## Getting Help

- **View all commands**: Type `/list` in the console
- **Check PHP configuration**: Type `/phpinfo` in the console
- **Review specification**: Read [spec.md](spec.md)
- **Report issues**: Check the repository issue tracker

---

**Version**: 1.0.0 | **Last Updated**: 2025-01-24
