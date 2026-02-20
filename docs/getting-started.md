# Getting Started

This guide walks you through deploying PHP Server Manager on your server.

## Prerequisites

- PHP 5.4 or higher
- Unix/Linux operating system (Ubuntu, Debian, CentOS, etc.)
- Web server (Apache, Nginx, or PHP built-in server)
- PHP session support enabled (enabled by default)
- Writable temp directory (`/tmp`)
- Modern web browser with JavaScript and cookies enabled

**Optional but recommended:**

- HTTPS enabled for secure password transmission
- PHP `exec()` function enabled for the `/exec` command

## Installation

### Step 1: Set Your Password

Open `server-manager.php` and change the password variable at the top of the file:

```php
// ===== CONFIGURATION: SET YOUR PASSWORD HERE =====
$ADMIN_PASSWORD = 'your-secure-password-here';
// =================================================
```

> **Important:** Choose a strong password (minimum 16 characters recommended). This file provides full server access to anyone who knows the password.

### Step 2: Upload to Server

Upload `server-manager.php` to your web server using FTP, SCP, or your hosting panel's file manager.

Example locations:

- `/var/www/html/server-manager.php`
- `/home/username/public_html/tools/server-manager.php`

Using SCP:

```bash
scp server-manager.php user@server:/var/www/html/
```

### Step 3: Access in Browser

Navigate to the file in your web browser:

```
https://your-domain.com/server-manager.php
```

### Step 4: Login

Enter the password you configured in Step 1 and press Enter.

### Step 5: Start Managing

You're in! Try these commands to get started:

```
> /list              # See all available commands
> ls                 # List current directory
> cd /var/www/html   # Navigate to a directory
> cat config.php     # View a file
```

## Using PHP Built-in Server (Development)

If you don't have Apache or Nginx, use PHP's built-in server:

```bash
cd /path/to/directory
php -S localhost:8000 server-manager.php
```

Access at: `http://localhost:8000/server-manager.php`

## Understanding the Interface

### Console Output

The terminal-style interface displays:

1. **Header:** "PHP Server Manager 1.0.0" shown at the top
2. **Command history:** All previous commands and their output
3. **Current directory:** Shown below the console output

### Input

At the bottom of the screen:

```
> [Type your command here]
```

Type a command and press **Enter** to execute. The page refreshes and shows the result. History persists across page refreshes.

## Next Steps

- [Command Reference](commands.md) — Full list of all commands
- [Security Guide](security.md) — Harden your deployment
- [Docker Guide](docker.md) — Run in a container for development/testing
