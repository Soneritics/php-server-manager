# Research: PHP Server Manager

**Date**: 2025-01-24  
**Phase**: 0 - Research & Technology Decisions

## Overview

This document consolidates technical research for implementing the PHP Server Manager single-file application. All decisions prioritize native PHP 5.4+ functionality without external dependencies.

---

## 1. Session Management

### Decision: PHP Native Sessions with File-Based Storage

**Rationale**: 
- Built-in PHP session management provides automatic state persistence
- Session IDs offer natural isolation per user
- Native garbage collection handles cleanup automatically (passive cleanup)
- No external dependencies required

**Implementation Pattern**:

```php
// Start session at top of script
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => false,  // Set true if HTTPS available
    'cookie_samesite' => 'Strict'
]);

// Store current directory in session
$_SESSION['current_dir'] = getcwd();

// Get session ID for temporary file naming
$session_id = session_id();
$console_file = sys_get_temp_dir() . "/console_output_{$session_id}.txt";
```

**Key Functions**:
- `session_start()` - Initialize session
- `session_id()` - Get current session ID
- `$_SESSION[]` - Store/retrieve session data
- `session_status()` - Check if session is active

**Cleanup Mechanism**:
- Rely on PHP's native `session.gc_maxlifetime` (default: 1440 seconds / 24 minutes)
- Garbage collection runs probabilistically based on `session.gc_probability` and `session.gc_divisor`
- No custom cleanup code needed - temporary files are cleaned when session expires

**Alternatives Considered**:
- Cookie-based state → Rejected: Insecure for storing directory paths
- URL parameters → Rejected: Doesn't persist across page refreshes
- Database storage → Rejected: Violates native-only constraint

---

## 2. File System Operations

### Decision: PHP Native Functions Over Shell Commands

**Rationale**:
- FR-009 explicitly requires `scandir()` and `stat()` for `ls` command
- Native functions avoid shell injection vulnerabilities
- Direct PHP functions are more reliable and portable
- Better error handling and type safety

**Implementation Mapping**:

| Command | PHP Native Functions | Shell Alternative |
|---------|---------------------|-------------------|
| `ls` | `scandir()`, `stat()`, `fileperms()`, `fileowner()`, `filesize()` | `exec('ls -la')` ❌ |
| `cd` | `chdir()`, `getcwd()` | N/A |
| `cat` | `file_get_contents()`, `readfile()` | `exec('cat')` ❌ |
| `mkdir` | `mkdir($dir, 0755, true)` | `exec('mkdir')` ❌ |
| `rm` | `unlink()`, `rmdir()`, recursive function | `exec('rm -rf')` ❌ |
| `cp` | `copy($src, $dst)` | `exec('cp')` ❌ |
| `ren` | `rename($old, $new)` | `exec('mv')` ❌ |
| `chmod` | `chmod($path, 0755)` | `exec('chmod')` ❌ |
| `chown` | `chown($path, $uid)`, `chgrp($path, $gid)` | `exec('chown')` ❌ |

**Directory Listing Format** (FR-009):

```php
function format_ls_output($dir) {
    $files = scandir($dir);
    $output = [];
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        $stat = stat($path);
        $perms = fileperms($path);
        $owner = posix_getpwuid(fileowner($path))['name'] ?? fileowner($path);
        $size = filesize($path);
        $mtime = date('Y-m-d H:i:s', filemtime($path));
        
        // Format similar to ls -la: permissions owner size date name
        $perm_string = format_permissions($perms);
        $output[] = sprintf("%-10s %-8s %10d %s %s", 
            $perm_string, $owner, $size, $mtime, $file);
    }
    
    return implode("\n", $output);
}

function format_permissions($perms) {
    // Convert numeric permissions to rwxr-xr-x format
    $info = '';
    $info .= (($perms & 0x4000) == 0x4000) ? 'd' : '-';
    $info .= (($perms & 0x0100) ? 'r' : '-');
    $info .= (($perms & 0x0080) ? 'w' : '-');
    $info .= (($perms & 0x0040) ? 'x' : '-');
    $info .= (($perms & 0x0020) ? 'r' : '-');
    $info .= (($perms & 0x0010) ? 'w' : '-');
    $info .= (($perms & 0x0008) ? 'x' : '-');
    $info .= (($perms & 0x0004) ? 'r' : '-');
    $info .= (($perms & 0x0002) ? 'w' : '-');
    $info .= (($perms & 0x0001) ? 'x' : '-');
    return $info;
}
```

**Path Security**:

```php
function safe_path($base, $user_input) {
    $real = realpath($base . DIRECTORY_SEPARATOR . $user_input);
    $base_real = realpath($base);
    
    // Prevent directory traversal
    if ($real === false || strpos($real, $base_real) !== 0) {
        return false;
    }
    return $real;
}
```

**Alternatives Considered**:
- Shell commands via exec() → Rejected: Higher security risk, violates FR-009
- DirectoryIterator class → Considered: Native option but scandir() is simpler
- glob() function → Rejected: Doesn't provide file metadata

---

## 3. Command Execution (/exec command)

### Decision: exec() with 3-Parameter Form and Whitelisting

**Rationale**:
- FR-020 requires `/exec` command for arbitrary shell execution
- `exec()` with output array provides best control
- Must balance security with the "execute immediately" requirement (FR-027)
- Whitelist approach inappropriate for "arbitrary" commands - rely on authentication

**Implementation Pattern**:

```php
function execute_command($cmd_string) {
    $output = [];
    $return_var = 0;
    
    // Execute command, capture all output
    exec($cmd_string . ' 2>&1', $output, $return_var);
    
    // FR-023: Display error messages directly
    if ($return_var !== 0) {
        return "Command failed with exit code {$return_var}:\n" . implode("\n", $output);
    }
    
    return implode("\n", $output);
}
```

**Security Approach**:
- Authenticated users are trusted (FR-026 password authentication)
- No confirmation prompts (FR-027: execute immediately)
- Display errors directly (FR-023: transparency and debuggability)
- User is responsible for consequences (per spec assumptions)

**Function Comparison**:

| Function | Output Capture | Best Use Case | Risk Level |
|----------|---------------|---------------|------------|
| `exec()` | Array (all lines) | Full output capture | Medium (if escaped) |
| `shell_exec()` | String (all output) | Simple string output | High (harder to control) |
| `system()` | Echoes directly | Legacy/immediate output | High (no capture) |
| `passthru()` | Binary output | Raw binary data | High (no capture) |

**Choice**: `exec()` with 3 parameters for maximum control and error handling.

**Alternatives Considered**:
- Command whitelist → Rejected: Spec requires "arbitrary" commands
- Input sanitization → Minimal: Authenticated admin is trusted
- proc_open() → Rejected: Too complex for requirements

---

## 4. Authentication

### Decision: Simple Password Variable with Session-Based State

**Rationale**:
- FR-026 requires "hardcoded password variable defined at top of PHP file"
- Spec clarifies "Simple password variable at the top of the file"
- Session-based state tracks authentication across requests
- Minimal barrier while maintaining single-file simplicity

**Implementation Pattern**:

```php
<?php
// ===== CONFIGURATION: SET YOUR PASSWORD HERE =====
$ADMIN_PASSWORD = 'your-secure-password-here';
// =================================================

session_start(['cookie_httponly' => true]);

// Check if already authenticated
if (!isset($_SESSION['authenticated'])) {
    // Show login form
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
        if ($_POST['password'] === $ADMIN_PASSWORD) {
            $_SESSION['authenticated'] = true;
            $_SESSION['login_time'] = time();
        } else {
            $error = 'Invalid password';
        }
    }
    
    // Display login form if not authenticated
    if (!isset($_SESSION['authenticated'])) {
        show_login_form($error ?? null);
        exit;
    }
}

// Optional: Session timeout (24 minutes to match session GC)
if (time() - ($_SESSION['login_time'] ?? 0) > 1440) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
```

**Password Storage**:
- Plain text password variable (per spec: "simple password variable")
- User sets password before uploading file
- No hashing required for this use case (trade-off for simplicity)
- Assumes HTTPS and trusted environment

**Security Notes** (per spec):
- "Minimal security barrier suitable for trusted environments only"
- User should implement additional controls (IP whitelist, HTTPS, VPN)
- Password visible in source code - file must be secured
- Remove from server after use or place behind additional security

**Alternatives Considered**:
- password_hash() → Rejected: Spec asks for "simple password variable", adds complexity
- HTTP Basic Auth → Rejected: Password sent on every request, less user-friendly
- Token-based auth → Rejected: Violates single-file simplicity

---

## 5. HTTP Operations

### Decision: Native Headers and readfile() for Downloads

**Rationale**:
- FR-021 requires `/download` command for file downloads
- Native `header()` and `readfile()` functions handle all requirements
- No external libraries needed
- Direct browser download support

**Implementation Pattern**:

```php
function download_file($filepath) {
    if (!file_exists($filepath) || !is_readable($filepath)) {
        return "Error: File not found or not readable";
    }
    
    // Determine MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $filepath) ?: 'application/octet-stream';
    finfo_close($finfo);
    
    // Send download headers
    header('Content-Type: ' . $mime);
    header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
    header('Content-Length: ' . filesize($filepath));
    header('Cache-Control: no-cache, must-revalidate');
    
    // Stream file to browser
    readfile($filepath);
    exit;
}
```

**MIME Type Detection**:
- Use `finfo_file()` for accurate MIME detection
- Fallback to `application/octet-stream` for unknown types
- Supports all file types automatically

**Form Handling**:

```php
// POST request handling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['command'])) {
    $command = trim($_POST['command']);
    
    // Process command
    $output = process_command($command);
    
    // Append to console output file
    append_console_output($output);
}
```

**Alternatives Considered**:
- X-Sendfile header → Rejected: Requires server configuration
- Data URLs → Rejected: Not suitable for large files
- JavaScript blob download → Rejected: Requires external JS, less reliable

---

## 6. Console Output Persistence

### Decision: Session-Based Temporary Files

**Rationale**:
- FR-006 requires temporary file storage with session ID-based filenames
- FR-006 specifies passive cleanup via PHP's native session garbage collection
- Provides per-user isolation automatically
- No custom cleanup code needed

**Implementation Pattern**:

```php
function get_console_file() {
    $session_id = session_id();
    return sys_get_temp_dir() . "/console_output_{$session_id}.txt";
}

function append_console_output($text) {
    $file = get_console_file();
    file_put_contents($file, $text . "\n", FILE_APPEND | LOCK_EX);
}

function read_console_output() {
    $file = get_console_file();
    if (file_exists($file)) {
        return file_get_contents($file);
    }
    return "PHP Server Manager 1.0.0\n";  // FR-003: Initial header
}

function clear_console_output() {
    $file = get_console_file();
    if (file_exists($file)) {
        unlink($file);
    }
    // Re-initialize with header
    append_console_output("PHP Server Manager 1.0.0");
}
```

**Cleanup Strategy**:
- Rely on PHP's `session.gc_maxlifetime` setting (typically 1440 seconds)
- Session garbage collection runs based on `session.gc_probability`/`session.gc_divisor`
- When session expires, temporary file becomes eligible for cleanup
- No custom cleanup code required (per FR-006)

**File Locking**:
- Use `LOCK_EX` flag for safe concurrent writes
- Prevents race conditions if multiple requests somehow occur

**Alternatives Considered**:
- Database storage → Rejected: Violates native-only constraint
- Cookie storage → Rejected: 4KB size limit inadequate
- Memory only → Rejected: Doesn't persist across requests

---

## 7. UI/UX Design

### Decision: Single-Page Console Interface

**Rationale**:
- FR-002: Console-style interface with black background and monospace font
- FR-003: Display "PHP Server Manager 1.0.0" on every page load
- FR-004: Input field at bottom with ">" character
- FR-025: Placeholder text for discoverability

**CSS Implementation**:

```css
body {
    background-color: #000;
    color: #0f0;  /* Green text for console aesthetic */
    font-family: 'Cascadia Mono', 'Lucida Console', 'Consolas', 'Courier New', monospace;
    margin: 0;
    padding: 20px;
}

#console {
    white-space: pre-wrap;  /* Preserve formatting */
    font-size: 14px;
    line-height: 1.4;
    margin-bottom: 20px;
}

#input-container {
    display: flex;
    align-items: center;
}

#prompt {
    margin-right: 10px;
    color: #0f0;
    font-size: 18px;
}

#command-input {
    flex: 1;
    background-color: #000;
    color: #0f0;
    border: none;
    outline: none;
    font-family: inherit;
    font-size: 14px;
}
```

**Form Submission**:

```php
<form method="POST" id="console-form">
    <div id="console"><?php echo htmlspecialchars($console_output); ?></div>
    <div id="current-dir">Current directory: <?php echo getcwd(); ?></div>
    <div id="input-container">
        <span id="prompt">&gt;</span>
        <input type="text" name="command" id="command-input" 
               placeholder="Type your command or /list to list all" 
               autofocus autocomplete="off">
    </div>
</form>

<script>
// Submit on Enter key
document.getElementById('command-input').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        document.getElementById('console-form').submit();
    }
});
</script>
```

**Alternatives Considered**:
- Multi-page navigation → Rejected: Spec requires single-page application
- AJAX updates → Rejected: Adds complexity, POST form simpler
- Fancy terminal emulator → Rejected: Over-engineered for requirements

---

## 8. Error Handling

### Decision: Direct PHP Error Display

**Rationale**:
- FR-023: "Display PHP error messages directly in console output"
- FR-023 purpose: "Transparency and debuggability"
- No confirmation prompts (FR-027)
- User is trusted authenticated admin

**Implementation Pattern**:

```php
function handle_command($command) {
    try {
        // Attempt command execution
        $result = execute_command_internal($command);
        return $result;
    } catch (Exception $e) {
        return "Error: " . $e->getMessage();
    } catch (Throwable $t) {
        return "Fatal Error: " . $t->getMessage();
    }
}

// For specific operations
function safe_mkdir($path) {
    if (!@mkdir($path, 0755, true)) {
        $error = error_get_last();
        return "mkdir failed: " . ($error['message'] ?? 'Unknown error');
    }
    return "Directory created: $path";
}
```

**Error Suppression**:
- Use `@` operator to suppress PHP warnings
- Capture error with `error_get_last()`
- Format for console display
- Maintains clean output while providing information

**Edge Cases** (from spec):
- Invalid permissions → Display PHP error directly
- Non-existent files → Display PHP error directly
- Invalid command syntax → Display PHP error directly
- Empty commands → Ignore or display empty line

**Alternatives Considered**:
- Structured error objects → Rejected: Over-engineered for console output
- Error codes → Rejected: Text messages more user-friendly
- Logging to file → Rejected: Console display is the log

---

## Technology Stack Summary

| Component | Technology | Rationale |
|-----------|-----------|-----------|
| Language | PHP 5.4+ | Spec requirement; maximum compatibility |
| Storage | File system (temp files) | Native; session-based isolation |
| State | PHP Sessions ($_SESSION) | Native; secure; automatic cleanup |
| File Ops | scandir(), stat(), etc. | Native; avoids shell injection |
| Command Exec | exec() with 3 params | Native; best output control |
| Authentication | Session + password variable | Simple; per spec requirement |
| Downloads | header() + readfile() | Native; full MIME support |
| UI | HTML + CSS (inline) | Single-file constraint |
| Forms | POST with native handling | No AJAX needed; simpler |

---

## Implementation Priorities

Based on user story priorities (P1-P5) from spec:

1. **P1 - Foundation**: File system navigation (ls, cd) + session management
2. **P2 - Viewing**: File content viewing (cat)
3. **P3 - Modification**: Creation (mkdir) and deletion (rm)
4. **P4 - Organization**: Copy/rename (cp, ren) + permissions (chmod, chown)
5. **P4 - Download**: File download (/download)
6. **P5 - Advanced**: Command execution (/exec), system info (/phpinfo, /list, /clear)

---

## Risks and Mitigations

| Risk | Mitigation |
|------|-----------|
| Directory traversal attacks | Use `realpath()` validation on all path operations |
| Command injection via /exec | Authenticated users only; no input sanitization (trusted) |
| Session hijacking | HttpOnly cookies; optional HTTPS; session timeout |
| Large console output files | Automatic cleanup via session GC (24 min timeout) |
| PHP execution time limits | Bounded by php.ini max_execution_time; acceptable per spec |
| Insufficient file permissions | Display PHP error; user must configure server permissions |
| Script self-modification | No special handling; user responsible (per spec) |

---

## Open Questions

None. All technical decisions resolved:
- ✅ Session strategy: Native PHP sessions with $_SESSION
- ✅ Temp file cleanup: Passive cleanup via session GC
- ✅ Current directory persistence: $_SESSION storage
- ✅ `ls` implementation: scandir() + stat() with text formatting
- ✅ Platform target: Unix/Linux only
- ✅ Authentication: Simple password variable + session state
- ✅ Error handling: Direct display with transparency

---

## Next Steps

Proceed to **Phase 1: Design & Contracts**:
1. Generate `data-model.md` - Define entity structures
2. Generate `contracts/` - Define command interface contracts
3. Generate `quickstart.md` - Setup and usage guide
4. Update agent context with technology stack
