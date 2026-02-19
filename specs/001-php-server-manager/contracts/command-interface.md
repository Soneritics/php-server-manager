# Command Interface Contract

**Version**: 1.0.0  
**Date**: 2025-01-24  
**Type**: Internal API Contract

## Overview

This document defines the internal API contract for command handling in the PHP Server Manager. All commands follow a consistent interface pattern for parsing, execution, and result formatting.

---

## Command Handler Interface

### Function Signature

```php
function handle_command(string $command_string): CommandResult
```

**Parameters**:
- `$command_string` (string): Raw command input from user (trimmed)

**Returns**:
- `CommandResult` object with success status, output text, error message, and optional action

**Example**:
```php
$result = handle_command("ls -la");
if ($result->success) {
    echo $result->output;  // Display file listing
} else {
    echo $result->error;   // Display error message
}
```

---

## Command Result Structure

```php
class CommandResult {
    public bool $success;       // True if command succeeded
    public string $output;      // Output text to display in console
    public ?string $error;      // Error message if failed (null if success)
    public ?string $action;     // Special action: "download", "phpinfo", etc.
}
```

## Supported Commands Summary

1. **ls** - List directory contents with detailed info (permissions, owner, size, date, name)
2. **cd** - Change current directory
3. **cat** - Display file contents
4. **mkdir** - Create directory
5. **rm** - Remove file or directory (recursive)
6. **cp** - Copy file
7. **ren** - Rename file or directory
8. **chmod** - Change file permissions
9. **chown** - Change file ownership
10. **/exec** - Execute arbitrary shell command
11. **/download** - Download file to browser
12. **/phpinfo** - Display PHP configuration
13. **/list** - List all available commands
14. **/clear** - Clear console output

For complete command specifications including syntax, arguments, success output formats, error cases, and behaviors, see the full contract documentation.
