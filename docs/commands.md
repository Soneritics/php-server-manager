# Command Reference

Complete reference for all PHP Server Manager commands.

## File System Navigation

### `ls` — List Directory Contents

```
ls [path]
```

Lists files and directories with permissions, owner, size, and modification date.

| Argument | Required | Description |
|----------|----------|-------------|
| `path` | No | Directory to list. Defaults to current directory. |

**Examples:**

```
> ls
> ls /var/www/html
> ls ..
```

### `cd` — Change Directory

```
cd <path>
```

Changes the current working directory. Supports absolute and relative paths.

| Argument | Required | Description |
|----------|----------|-------------|
| `path` | Yes | Target directory (absolute or relative). |

**Examples:**

```
> cd /var/www/html
> cd subdir
> cd ..
```

### `cat` — View File Contents

```
cat <file>
```

Displays the contents of a text file in the console.

| Argument | Required | Description |
|----------|----------|-------------|
| `file` | Yes | Path to the file to view. |

**Examples:**

```
> cat index.php
> cat /etc/hostname
> cat ../config.ini
```

## File Operations

### `mkdir` — Create Directory

```
mkdir <dir>
```

Creates a new directory. Supports recursive creation (nested directories).

| Argument | Required | Description |
|----------|----------|-------------|
| `dir` | Yes | Directory path to create. |

**Examples:**

```
> mkdir backups
> mkdir path/to/nested/dir
```

### `rm` — Remove File or Directory

```
rm <path>
```

Deletes a file or directory. Directories are removed recursively. **There is no confirmation prompt — deletion is immediate.**

| Argument | Required | Description |
|----------|----------|-------------|
| `path` | Yes | File or directory to remove. |

**Examples:**

```
> rm old-file.txt
> rm temp-directory
```

### `cp` — Copy File

```
cp <source> <destination>
```

Copies a file to a new location. Does not overwrite existing files.

| Argument | Required | Description |
|----------|----------|-------------|
| `source` | Yes | Source file path. |
| `destination` | Yes | Destination file path. |

**Examples:**

```
> cp config.php config.php.bak
> cp /var/www/html/index.php /tmp/index.php
```

### `ren` — Rename File or Directory

```
ren <old> <new>
```

Renames a file or directory. Fails if the destination already exists.

| Argument | Required | Description |
|----------|----------|-------------|
| `old` | Yes | Current name/path. |
| `new` | Yes | New name/path. |

**Examples:**

```
> ren old-name.txt new-name.txt
> ren temp-dir final-dir
```

## Permission Management

### `chmod` — Change Permissions

```
chmod <mode> <path>
```

Changes file or directory permissions using octal notation.

| Argument | Required | Description |
|----------|----------|-------------|
| `mode` | Yes | Octal permission mode (e.g., `755`, `644`). |
| `path` | Yes | File or directory path. |

**Common permission modes:**

| Mode | Description |
|------|-------------|
| `755` | Owner: rwx, Group: rx, Others: rx (typical for directories and scripts) |
| `644` | Owner: rw, Group: r, Others: r (typical for files) |
| `600` | Owner: rw (private files, secrets) |
| `777` | Everyone: rwx (use with caution) |

**Examples:**

```
> chmod 755 script.sh
> chmod 644 config.php
> chmod 600 secrets.key
```

### `chown` — Change Ownership

```
chown <user:group> <path>
```

Changes file or directory ownership. Requires appropriate privileges (typically root).

| Argument | Required | Description |
|----------|----------|-------------|
| `user:group` | Yes | Owner and group in `user:group` format. Group is optional. |
| `path` | Yes | File or directory path. |

**Examples:**

```
> chown www-data:www-data index.php
> chown root:root config.php
> chown www-data upload.txt
```

## Special Commands

### `/exec` — Execute Shell Command

```
/exec <command>
```

Executes an arbitrary shell command and displays the output. Captures both stdout and stderr.

| Argument | Required | Description |
|----------|----------|-------------|
| `command` | Yes | Shell command to execute. |

**Examples:**

```
> /exec whoami
> /exec df -h
> /exec ps aux
> /exec tail -20 /var/log/syslog
> /exec cd /var/www && ls -la && pwd
```

> **Note:** Requires the PHP `exec()` function to be enabled. Displays exit code on non-zero return.

### `/download` — Download File

```
/download <file>
```

Downloads a file to your browser. Automatically detects MIME type.

| Argument | Required | Description |
|----------|----------|-------------|
| `file` | Yes | Path to the file to download. |

**Examples:**

```
> /download access.log
> /download /var/www/html/backup.sql
```

### `/phpinfo` — PHP Configuration

```
/phpinfo
```

Displays the full PHP configuration page (equivalent to `phpinfo()`). Opens in the browser.

### `/list` — List All Commands

```
/list
```

Displays all available commands with syntax and descriptions.

### `/clear` — Clear Console

```
/clear
```

Clears all console output and resets to the header.

### `/autodestruct` — Self-Destruct

```
/autodestruct
```

**Permanently deletes `server-manager.php` from the server.** Use this when you're done with maintenance to remove the tool from your server.

> **Warning:** This action is irreversible. The script file is deleted immediately.
