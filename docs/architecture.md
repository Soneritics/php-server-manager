# Architecture

Technical overview of how PHP Server Manager works internally.

## Design Principles

- **Single file** — The entire application lives in one PHP file with no external dependencies.
- **Native PHP only** — Uses only built-in PHP functions. No Composer packages, no npm modules.
- **Session-based state** — Working directory and console output persist across requests using PHP sessions.
- **Stateless commands** — Each command execution is independent; the server processes one command per HTTP request.

## Request Lifecycle

1. **Session start** — PHP session is initialized with HttpOnly and SameSite cookie settings.
2. **Authentication check** — If not authenticated, display the login form and exit.
3. **Session timeout check** — If the session is older than 24 minutes, destroy it and redirect.
4. **Command processing** — If a POST request with a `command` field is received:
   - Parse the command string into name and arguments.
   - Route to the appropriate handler function.
   - Handle special actions (download, phpinfo, autodestruct, clear).
   - Append command and output to the console output file.
   - Redirect (POST/Redirect/GET pattern) to prevent form resubmission.
5. **Render** — Display the console interface with current output and directory.

## Key Components

### Authentication

Password comparison against the `$ADMIN_PASSWORD` variable. Successful login sets `$_SESSION['authenticated']` and `$_SESSION['login_time']`.

### Command Parser

Splits the input string on the first space into a command name and arguments string:

```
"chmod 755 file.txt" → name: "chmod", args: "755 file.txt"
```

### Command Router

A `switch` statement maps command names to handler functions. Unknown commands return an error with a hint to use `/list`.

### CommandResult

A simple class that handlers return:

| Property | Type | Description |
|----------|------|-------------|
| `success` | bool | Whether the command succeeded |
| `output` | string | Text output to display |
| `error` | string | Error message (if failed) |
| `action` | string | Special action trigger (e.g., `download:path`, `phpinfo`, `clear`, `autodestruct`) |

### Console Output Storage

Output is stored in a temporary file (`/tmp/console_output_{session_id}.txt`). This allows output to persist across HTTP requests without storing large text in the session.

### Path Safety

The `safe_path()` function resolves paths using `realpath()` to prevent directory traversal attacks. Relative paths are resolved against the current working directory stored in the session.

## File Structure

```
server-manager.php    # Complete application (PHP, HTML, CSS, JavaScript)
├── PHP Backend
│   ├── Configuration (password)
│   ├── Session management
│   ├── Authentication
│   ├── Command parser
│   ├── Command router
│   ├── 13 command handlers
│   ├── Helper functions (permissions, paths, console I/O)
│   └── Request processing & special action handling
├── HTML Structure
│   ├── Login page (shown when unauthenticated)
│   └── Console interface (shown when authenticated)
├── CSS Styling
│   └── Terminal theme (black background, green text, monospace)
└── JavaScript
    ├── Enter key form submission
    └── Auto-scroll to bottom
```

## Technology Stack

| Component | Technology |
|-----------|-----------|
| Language | PHP 5.4+ |
| Server | Apache, Nginx, or PHP built-in |
| Platform | Unix/Linux |
| Frontend | Inline HTML, CSS, JavaScript |
| State | PHP sessions + temp files |
| Dependencies | None |
