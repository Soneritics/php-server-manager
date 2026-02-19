# Data Model: PHP Server Manager

**Date**: 2025-01-24  
**Phase**: 1 - Design & Contracts

## Overview

This document defines the core entities, data structures, and state management for the PHP Server Manager application. Since this is a single-file PHP application with no database, the "data model" focuses on in-memory structures, session state, and file system interactions.

---

## 1. Session State

### 1.1 Session Variables

Stored in `$_SESSION` superglobal across requests:

```php
$_SESSION = [
    'authenticated' => bool,           // Authentication status
    'login_time' => int,               // Unix timestamp of login
    'current_dir' => string,           // Current working directory path
];
```

**Fields**:
- `authenticated` (boolean): Whether user has successfully authenticated
  - **Default**: `false` (not set)
  - **Set on**: Successful password verification
  - **Cleared on**: Session timeout or explicit logout
  
- `login_time` (integer): Unix timestamp when authentication occurred
  - **Purpose**: Enable session timeout calculation
  - **Validation**: `time() - login_time > 1440` triggers re-authentication
  
- `current_dir` (string): Absolute path to current working directory
  - **Default**: `getcwd()` at session start
  - **Updated on**: Successful `cd` command
  - **Validation**: Must be a valid, readable directory path

**Lifecycle**:
- Created: First request after `session_start()`
- Updated: On each command that modifies state (cd)
- Destroyed: Session timeout (24 min) or explicit `/logout` command (if implemented)

---

## 2. Console Output

### 2.1 Console Output File

Temporary file storing accumulated console text:

```php
// File path structure
$console_file_path = sys_get_temp_dir() . "/console_output_" . session_id() . ".txt";

// Example: /tmp/console_output_abc123def456.txt
```

**Properties**:
- **Location**: System temp directory (`sys_get_temp_dir()`)
- **Naming**: `console_output_{session_id}.txt`
- **Format**: Plain text with newline separators
- **Encoding**: UTF-8
- **Permissions**: 0644 (readable by web server user)

**Content Structure**:
```text
PHP Server Manager 1.0.0
[First command output]
[Second command output]
[Current working directory line]
```

**Operations**:
- **Read**: `file_get_contents($console_file_path)`
- **Append**: `file_put_contents($console_file_path, $text, FILE_APPEND | LOCK_EX)`
- **Clear**: `unlink($console_file_path)` then reinitialize
- **Cleanup**: Automatic via PHP session garbage collection

---

## 3. Command Entity

### 3.1 Command Structure

Represents a parsed user command:

```php
class Command {
    public string $name;        // Command name (e.g., "ls", "cd", "/exec")
    public array $args;         // Command arguments (e.g., ["subdir"])
    public string $raw;         // Original raw input string
}
```

**Example Parsing**:
```php
Input: "cd /var/www"
→ name: "cd"
→ args: ["/var/www"]
→ raw: "cd /var/www"

Input: "/exec ls -la /tmp"
→ name: "/exec"
→ args: ["ls -la /tmp"]  // Everything after /exec
→ raw: "/exec ls -la /tmp"

Input: "ls"
→ name: "ls"
→ args: []
→ raw: "ls"
```

**Validation Rules**:
- `name` must be non-empty after trim
- `name` matched against supported command list
- `args` may be empty array for commands without arguments
- `/` prefix indicates special commands (exec, download, phpinfo, etc.)

### 3.2 Command Types

**File System Commands** (no `/` prefix):
- `ls` - List directory contents
- `cd` - Change directory
- `cat` - Display file contents
- `mkdir` - Create directory
- `rm` - Remove file/directory
- `cp` - Copy file
- `ren` - Rename file/directory
- `chmod` - Change permissions
- `chown` - Change ownership

**Special Commands** (`/` prefix):
- `/exec` - Execute arbitrary shell command
- `/download` - Download file to browser
- `/phpinfo` - Display PHP configuration
- `/list` - List all available commands
- `/clear` - Clear console output

---

## 4. File System Entry

### 4.1 File/Directory Metadata

Represents a file or directory with its attributes (used for `ls` command):

```php
class FileEntry {
    public string $name;            // File/directory name
    public string $path;            // Absolute path
    public int $size;               // Size in bytes
    public int $mtime;              // Last modification time (Unix timestamp)
    public int $perms;              // Numeric permissions (e.g., 0755)
    public string $perms_string;    // Human-readable (e.g., "drwxr-xr-x")
    public int $owner_id;           // Owner user ID
    public string $owner_name;      // Owner username (if resolvable)
    public int $group_id;           // Group ID
    public string $group_name;      // Group name (if resolvable)
    public bool $is_dir;            // True if directory
}
```

**Population from PHP Functions**:
```php
$file_entry = new FileEntry();
$file_entry->name = basename($path);
$file_entry->path = $path;
$file_entry->size = filesize($path);
$file_entry->mtime = filemtime($path);
$file_entry->perms = fileperms($path);
$file_entry->perms_string = format_permissions($file_entry->perms);
$file_entry->owner_id = fileowner($path);
$file_entry->owner_name = posix_getpwuid($file_entry->owner_id)['name'] ?? $file_entry->owner_id;
$file_entry->group_id = filegroup($path);
$file_entry->group_name = posix_getgrgid($file_entry->group_id)['name'] ?? $file_entry->group_id;
$file_entry->is_dir = is_dir($path);
```

**Display Format** (ls -la style):
```text
drwxr-xr-x  www-data      4096  2025-01-24 14:30:00  subdir
-rw-r--r--  www-data     12345  2025-01-24 14:25:30  file.txt
```

---

## 5. Command Result

### 5.1 Result Structure

Represents the output of a command execution:

```php
class CommandResult {
    public bool $success;       // True if command succeeded
    public string $output;      // Output text to display
    public ?string $error;      // Error message if failed
    public ?string $action;     // Special action (e.g., "download", "redirect")
}
```

**Examples**:

**Success**:
```php
$result = new CommandResult();
$result->success = true;
$result->output = "file1.txt\nfile2.txt\ndir1/";
$result->error = null;
$result->action = null;
```

**Error**:
```php
$result = new CommandResult();
$result->success = false;
$result->output = "";
$result->error = "mkdir: Permission denied";
$result->action = null;
```

**Special Action (Download)**:
```php
$result = new CommandResult();
$result->success = true;
$result->output = "";
$result->error = null;
$result->action = "download:/path/to/file.txt";
```

---

## 6. Authentication State

### 6.1 Password Configuration

Single hardcoded password variable:

```php
// At top of PHP file
$ADMIN_PASSWORD = 'your-secure-password-here';
```

**Properties**:
- **Type**: Plain text string (per spec simplicity requirement)
- **Storage**: In-file variable (not in database or external config)
- **Comparison**: Direct string comparison (no hashing)
- **Set by**: User manually editing the PHP file before deployment

### 6.2 Login Flow State

```php
// Not authenticated
$_SESSION['authenticated'] === false (or not set)
→ Display login form
→ Accept password POST
→ Compare with $ADMIN_PASSWORD

// Authentication success
$_SESSION['authenticated'] = true
$_SESSION['login_time'] = time()
→ Display console interface

// Session timeout check
if (time() - $_SESSION['login_time'] > 1440) {
    session_destroy();
    → Redirect to login
}
```

---

## 7. State Transitions

### 7.1 Application State Machine

```
┌─────────────────┐
│  Initial Load   │
└────────┬────────┘
         │
         ▼
┌─────────────────┐     Password         ┌──────────────────┐
│  Unauthenticated├────────────────────▶│  Authenticated   │
└─────────────────┘     Incorrect        └────────┬─────────┘
         ▲             Password                    │
         │              │                          │ Command
         └──────────────┘                          │ Submission
                                                   │
                ┌───────────────────────────────┬──┴─────────┐
                │                               │            │
                ▼                               ▼            ▼
         ┌────────────┐              ┌──────────────┐  ┌──────────┐
         │   Timeout  │              │Command Exec  │  │ Download │
         │  (1440s)   │              │              │  │  Action  │
         └────────────┘              └──────────────┘  └──────────┘
```

### 7.2 Directory Navigation State

```
Current Directory: /var/www/html (stored in $_SESSION['current_dir'])
                    │
                    │ cd subdir
                    ▼
                  /var/www/html/subdir
                    │
                    │ cd ..
                    ▼
                  /var/www/html
                    │
                    │ cd /tmp
                    ▼
                  /tmp
```

**State Validation**:
- Directory must exist: `is_dir($new_path)`
- Directory must be readable: `is_readable($new_path)`
- Path must resolve to real location: `realpath($new_path)`

### 7.3 Console Output State

```
Empty Console
  │
  │ First command: ls
  ▼
"PHP Server Manager 1.0.0\nfile1.txt\nfile2.txt\n"
  │
  │ Second command: cd subdir
  ▼
"PHP Server Manager 1.0.0\nfile1.txt\nfile2.txt\nChanged to: /var/www/html/subdir\n"
  │
  │ /clear command
  ▼
"PHP Server Manager 1.0.0\n"
```

---

## 8. Data Constraints

### 8.1 Session Constraints

- **Session ID Length**: 32-128 characters (PHP default)
- **Session Timeout**: 1440 seconds (24 minutes, PHP default)
- **Session File Location**: PHP's session.save_path (typically /var/lib/php/sessions)

### 8.2 File System Constraints

- **Path Length**: Maximum 4096 characters (Linux PATH_MAX)
- **Filename Length**: Maximum 255 characters (Linux NAME_MAX)
- **File Size**: No application limit (PHP/server memory limits apply)
- **Directory Depth**: No application limit (filesystem limits apply)

### 8.3 Console Output Constraints

- **Max File Size**: No explicit limit; grows unbounded until session expires
- **Expected Size**: Typically <100KB for normal usage
- **Encoding**: UTF-8
- **Line Endings**: Unix-style LF (\n)

### 8.4 Command Constraints

- **Command Length**: No explicit limit; PHP POST size limits apply (typically 8MB)
- **Argument Count**: No explicit limit
- **Command Execution Time**: Bounded by php.ini `max_execution_time` (typically 30-300s)

---

## 9. Data Flow Diagrams

### 9.1 Request Processing Flow

```
User Browser
    │
    │ POST: command="ls"
    ▼
PHP Script Entry Point
    │
    ├─▶ Session Management (session_start)
    │   └─▶ Check $_SESSION['authenticated']
    │
    ├─▶ Unauthenticated?
    │   └─▶ Display Login Form → EXIT
    │
    ├─▶ Authenticated
    │   └─▶ Parse Command from $_POST['command']
    │       │
    │       ├─▶ Route to Command Handler
    │       │   │
    │       │   ├─▶ ls → scandir() + format
    │       │   ├─▶ cd → chdir() + update $_SESSION
    │       │   ├─▶ cat → file_get_contents()
    │       │   ├─▶ /exec → exec()
    │       │   └─▶ /download → header() + readfile() → EXIT
    │       │
    │       └─▶ Append Result to Console Output File
    │
    └─▶ Render HTML
        └─▶ Display Console Output + Input Form
            └─▶ Send to Browser
```

### 9.2 File Operation Flow

```
User Command: "ls"
    │
    ▼
Get Current Directory from $_SESSION['current_dir']
    │
    ▼
scandir($current_dir)
    │
    ▼
For Each File:
    │
    ├─▶ stat($file)
    ├─▶ fileperms($file)
    ├─▶ fileowner($file)
    ├─▶ filesize($file)
    ├─▶ filemtime($file)
    │
    └─▶ Format as "permissions owner size date name"
    │
    ▼
Combine All Lines
    │
    ▼
Return Formatted Output
```

---

## 10. Entity Relationships

### 10.1 Relationship Diagram

```
┌───────────────┐
│   Session     │
│   (PHP)       │
└───┬───────────┘
    │ 1:1
    │ contains
    ▼
┌───────────────┐
│ Session State │
│ - authenticated│
│ - login_time  │
│ - current_dir │
└───┬───────────┘
    │ 1:1
    │ references
    ▼
┌───────────────┐         ┌─────────────────┐
│Console Output │         │  File System    │
│     File      │         │   (Unix/Linux)  │
└───────────────┘         └────┬────────────┘
                               │ N:1
                               │ contains
                               ▼
                          ┌─────────────────┐
                          │  File Entries   │
                          │  (directories,  │
                          │   files)        │
                          └─────────────────┘
```

---

## 11. Validation Rules

### 11.1 Input Validation

**Command Name**:
- Must not be empty after trim
- Must match one of the supported command names
- Case-sensitive

**File Paths**:
- Must resolve with `realpath()` to valid path
- For `cd`: Must be directory (`is_dir()`)
- For `cat`: Must be file (`is_file()`)
- For `rm`, `chmod`, `chown`: Must exist (`file_exists()`)

**Password**:
- Must match `$ADMIN_PASSWORD` exactly (case-sensitive)
- No length limits enforced (user-defined)

### 11.2 Output Validation

**Console Output**:
- Escape HTML special characters with `htmlspecialchars()`
- Preserve whitespace and newlines
- UTF-8 encoding

**File Downloads**:
- Verify file exists and is readable
- Validate MIME type
- Sanitize filename in Content-Disposition header

---

## 12. Error States

### 12.1 Authentication Errors

```php
// Invalid password
$_SESSION['authenticated'] = false (remains)
Display: "Invalid password" error message
Action: Show login form again
```

### 12.2 Command Errors

```php
// File not found
"Error: cat: file.txt: No such file or directory"

// Permission denied
"Error: mkdir: /root/newdir: Permission denied"

// Invalid command
"Error: Unknown command: 'invalid'"
```

### 12.3 System Errors

```php
// Session start failure
if (session_status() !== PHP_SESSION_ACTIVE) {
    die("Error: Unable to start session");
}

// Temp file write failure
if (!@file_put_contents($console_file, $text, FILE_APPEND)) {
    // Log error but continue (non-critical)
    $error = error_get_last();
}
```

---

## 13. Data Persistence Strategy

### 13.1 Persistence Locations

| Data Type | Storage Location | Lifetime | Cleanup |
|-----------|-----------------|----------|---------|
| Session state | PHP session files (`/var/lib/php/sessions`) | 24 min (session.gc_maxlifetime) | Automatic (session GC) |
| Console output | Temp files (`/tmp/console_output_*`) | 24 min (tied to session) | Automatic (session GC) |
| Current directory | `$_SESSION['current_dir']` | Session lifetime | Automatic |
| Authentication | `$_SESSION['authenticated']` | Session lifetime | Automatic |
| Password | PHP source code (`$ADMIN_PASSWORD`) | Permanent | Manual (file edit) |

### 13.2 No Database

**Rationale**: Per constitution Principle III (Native Methods Only) and single-file constraint:
- No external database connection
- No SQL queries
- No ORM or database libraries
- All data stored in PHP sessions and temporary files

---

## Summary

The PHP Server Manager uses a minimal data model focused on:
1. **Session state** for authentication and navigation context
2. **Temporary files** for console output persistence
3. **In-memory structures** for command parsing and result handling
4. **Native PHP functions** for all file system metadata

No complex entities or relationships are required. The application is stateless between requests except for session data, with all file system operations performed on-demand against the live Unix/Linux file system.
