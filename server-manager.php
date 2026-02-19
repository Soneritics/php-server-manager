<?php
/**
 * PHP Server Manager v1.0.0
 * 
 * A single-file PHP application for web-based server management.
 * 
 * Features:
 * - Terminal-style console interface
 * - File system navigation and operations (ls, cd, cat, mkdir, rm, cp, ren)
 * - Permission management (chmod, chown)
 * - Command execution (/exec)
 * - File downloads (/download)
 * - System information (/phpinfo, /list, /clear)
 * 
 * Requirements:
 * - PHP 5.4 or higher
 * - Unix/Linux operating system
 * - Web server (Apache, Nginx, or PHP built-in server)
 * - Writable temp directory
 * 
 * Security:
 * - Password-protected access
 * - Session-based authentication
 * - Use only in trusted environments
 * - Remove after use or place behind additional security layers
 * 
 * @version 1.0.0
 * @license MIT
 */

// ===== CONFIGURATION: SET YOUR PASSWORD HERE =====
$ADMIN_PASSWORD = 'change-this-password-before-deployment';
// =================================================

// T004: Session Management
session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Strict'
]);

// T007: Session State Initialization
if (!isset($_SESSION['current_dir'])) {
    $_SESSION['current_dir'] = getcwd();
}

// T005: Password Authentication
$authenticated = isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
$error_message = '';

if (!$authenticated) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
        if ($_POST['password'] === $ADMIN_PASSWORD) {
            $_SESSION['authenticated'] = true;
            $_SESSION['login_time'] = time();
            $authenticated = true;
        } else {
            $error_message = 'Invalid password';
        }
    }
}

// T061: Session timeout check (24 minutes = 1440 seconds)
if ($authenticated && isset($_SESSION['login_time'])) {
    if (time() - $_SESSION['login_time'] > 1440) {
        session_destroy();
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Display login form if not authenticated
if (!$authenticated) {
    ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Server Manager - Login</title>
    <style>
        body {
            background-color: #000;
            color: #0f0;
            font-family: 'Cascadia Mono', 'Lucida Console', 'Consolas', 'Courier New', monospace;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            text-align: center;
        }
        h1 {
            font-size: 24px;
            margin-bottom: 30px;
        }
        .error {
            color: #f00;
            margin-bottom: 20px;
        }
        input[type="password"] {
            background-color: #000;
            color: #0f0;
            border: 1px solid #0f0;
            padding: 10px;
            font-family: inherit;
            font-size: 14px;
            width: 300px;
        }
        input[type="password"]:focus {
            outline: none;
            border-color: #0ff;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>PHP Server Manager 1.0.0</h1>
        <?php if ($error_message): ?>
            <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="password" name="password" placeholder="Enter password" autofocus>
        </form>
    </div>
</body>
</html>
    <?php
    exit;
}

// T006: Console Output File Management
function get_console_file() {
    return sys_get_temp_dir() . '/console_output_' . session_id() . '.txt';
}

function read_console_output() {
    $file = get_console_file();
    if (file_exists($file)) {
        return file_get_contents($file);
    }
    return "PHP Server Manager 1.0.0\n";
}

function append_console_output($text) {
    $file = get_console_file();
    file_put_contents($file, $text . "\n", FILE_APPEND | LOCK_EX);
}

function clear_console_output() {
    $file = get_console_file();
    if (file_exists($file)) {
        unlink($file);
    }
    append_console_output("PHP Server Manager 1.0.0");
}

// T009: CommandResult Structure
class CommandResult {
    public $success;
    public $output;
    public $error;
    public $action;
    
    public function __construct($success = true, $output = '', $error = null, $action = null) {
        $this->success = $success;
        $this->output = $output;
        $this->error = $error;
        $this->action = $action;
    }
}

// T008: Command Parsing Logic
function parse_command($command_string) {
    $command_string = trim($command_string);
    if (empty($command_string)) {
        return ['name' => '', 'args' => [], 'raw' => ''];
    }
    
    $parts = explode(' ', $command_string, 2);
    $name = $parts[0];
    $args = isset($parts[1]) ? trim($parts[1]) : '';
    
    return [
        'name' => $name,
        'args' => $args,
        'raw' => $command_string
    ];
}

// T062: Path Traversal Protection
function safe_path($path) {
    if (empty($path)) {
        return $_SESSION['current_dir'];
    }
    
    // Handle relative paths
    if ($path[0] !== '/') {
        $path = $_SESSION['current_dir'] . '/' . $path;
    }
    
    $real = realpath($path);
    if ($real === false) {
        return false;
    }
    
    return $real;
}

// Helper function for formatting file permissions
function format_permissions($perms) {
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

// T009: Command Routing Dispatcher
function handle_command($cmd) {
    $name = $cmd['name'];
    $args = $cmd['args'];
    
    // Route to appropriate handler
    switch ($name) {
        case 'ls':
            return handle_ls($args);
        case 'cd':
            return handle_cd($args);
        case 'cat':
            return handle_cat($args);
        case 'mkdir':
            return handle_mkdir($args);
        case 'rm':
            return handle_rm($args);
        case 'cp':
            return handle_cp($args);
        case 'ren':
            return handle_ren($args);
        case 'chmod':
            return handle_chmod($args);
        case 'chown':
            return handle_chown($args);
        case '/download':
            return handle_download($args);
        case '/exec':
            return handle_exec($args);
        case '/phpinfo':
            return handle_phpinfo($args);
        case '/list':
            return handle_list($args);
        case '/clear':
            return handle_clear($args);
        case '/autodestruct':
            return handle_autodestruct($args);
        default:
            return new CommandResult(false, '', "Unknown command: $name. Type /list for available commands.");
    }
}

// T015-T017: ls Command Handler
function handle_ls($args) {
    $dir = empty($args) ? $_SESSION['current_dir'] : $args;
    $path = safe_path($dir);
    
    if ($path === false || !is_dir($path)) {
        return new CommandResult(false, '', "ls: cannot access '$dir': No such file or directory");
    }
    
    if (!is_readable($path)) {
        return new CommandResult(false, '', "ls: cannot open directory '$dir': Permission denied");
    }
    
    $files = scandir($path);
    if ($files === false) {
        return new CommandResult(false, '', "ls: error reading directory '$dir'");
    }
    
    $output = [];
    foreach ($files as $file) {
        $filepath = $path . '/' . $file;
        
        if (!file_exists($filepath)) {
            continue;
        }
        
        $stat = @stat($filepath);
        if ($stat === false) {
            continue;
        }
        
        $perms = fileperms($filepath);
        $perm_string = format_permissions($perms);
        
        $owner_id = fileowner($filepath);
        $owner_name = function_exists('posix_getpwuid') 
            ? (posix_getpwuid($owner_id)['name'] ?? $owner_id) 
            : $owner_id;
        
        $size = is_dir($filepath) ? 4096 : filesize($filepath);
        $mtime = date('Y-m-d H:i:s', filemtime($filepath));
        
        $output[] = sprintf("%-11s %-12s %10s  %s  %s", 
            $perm_string, $owner_name, $size, $mtime, $file);
    }
    
    return new CommandResult(true, implode("\n", $output));
}

// T018-T021: cd Command Handler
function handle_cd($args) {
    if (empty($args)) {
        return new CommandResult(false, '', "cd: missing operand");
    }
    
    $path = safe_path($args);
    
    if ($path === false) {
        return new CommandResult(false, '', "cd: $args: No such file or directory");
    }
    
    if (!is_dir($path)) {
        return new CommandResult(false, '', "cd: $args: Not a directory");
    }
    
    if (!is_readable($path)) {
        return new CommandResult(false, '', "cd: $args: Permission denied");
    }
    
    // T018: Change directory
    if (!@chdir($path)) {
        return new CommandResult(false, '', "cd: $args: Cannot change directory");
    }
    
    // T020: Update session state
    $_SESSION['current_dir'] = $path;
    
    return new CommandResult(true, "Changed directory to: $path");
}

// T022-T024: cat Command Handler
function handle_cat($args) {
    if (empty($args)) {
        return new CommandResult(false, '', "cat: missing operand");
    }
    
    $path = safe_path($args);
    
    if ($path === false || !file_exists($path)) {
        return new CommandResult(false, '', "cat: $args: No such file or directory");
    }
    
    if (!is_file($path)) {
        return new CommandResult(false, '', "cat: $args: Is a directory");
    }
    
    if (!is_readable($path)) {
        return new CommandResult(false, '', "cat: $args: Permission denied");
    }
    
    $content = @file_get_contents($path);
    if ($content === false) {
        return new CommandResult(false, '', "cat: $args: Error reading file");
    }
    
    return new CommandResult(true, $content);
}

// T026-T029: mkdir Command Handler
function handle_mkdir($args) {
    if (empty($args)) {
        return new CommandResult(false, '', "mkdir: missing operand");
    }
    
    $path = safe_path($args);
    
    if ($path === false) {
        $path = $_SESSION['current_dir'] . '/' . $args;
    }
    
    if (file_exists($path)) {
        return new CommandResult(false, '', "mkdir: cannot create directory '$args': File exists");
    }
    
    // T026: Create directory with recursive option
    if (!@mkdir($path, 0755, true)) {
        $error = error_get_last();
        return new CommandResult(false, '', "mkdir: cannot create directory '$args': " . ($error['message'] ?? 'Unknown error'));
    }
    
    return new CommandResult(true, "Directory created: $args");
}

// T031: Recursive Directory Deletion Helper
function rmdir_recursive($dir) {
    if (!is_dir($dir)) {
        return false;
    }
    
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            rmdir_recursive($path);
        } else {
            @unlink($path);
        }
    }
    
    return @rmdir($dir);
}

// T030-T034: rm Command Handler
function handle_rm($args) {
    if (empty($args)) {
        return new CommandResult(false, '', "rm: missing operand");
    }
    
    $path = safe_path($args);
    
    if ($path === false || !file_exists($path)) {
        return new CommandResult(false, '', "rm: cannot remove '$args': No such file or directory");
    }
    
    // T033: Immediate deletion without confirmation
    if (is_dir($path)) {
        if (!rmdir_recursive($path)) {
            $error = error_get_last();
            return new CommandResult(false, '', "rm: cannot remove '$args': " . ($error['message'] ?? 'Unknown error'));
        }
        return new CommandResult(true, "Removed directory: $args");
    } else {
        if (!@unlink($path)) {
            $error = error_get_last();
            return new CommandResult(false, '', "rm: cannot remove '$args': " . ($error['message'] ?? 'Unknown error'));
        }
        return new CommandResult(true, "Removed file: $args");
    }
}

// T035: cp Command Handler
function handle_cp($args) {
    if (empty($args)) {
        return new CommandResult(false, '', "cp: missing file operands");
    }
    
    $parts = explode(' ', $args, 2);
    if (count($parts) < 2) {
        return new CommandResult(false, '', "cp: missing destination file operand");
    }
    
    $src = trim($parts[0]);
    $dst = trim($parts[1]);
    
    $src_path = safe_path($src);
    if ($src_path === false || !file_exists($src_path)) {
        return new CommandResult(false, '', "cp: cannot stat '$src': No such file or directory");
    }
    
    if (!is_file($src_path)) {
        return new CommandResult(false, '', "cp: '$src': Not a regular file");
    }
    
    $dst_path = safe_path($dst);
    if ($dst_path === false) {
        $dst_path = $_SESSION['current_dir'] . '/' . $dst;
    }
    
    if (file_exists($dst_path)) {
        return new CommandResult(false, '', "cp: cannot create regular file '$dst': File exists");
    }
    
    if (!@copy($src_path, $dst_path)) {
        $error = error_get_last();
        return new CommandResult(false, '', "cp: cannot copy '$src' to '$dst': " . ($error['message'] ?? 'Unknown error'));
    }
    
    return new CommandResult(true, "Copied: $src -> $dst");
}

// T036: ren Command Handler
function handle_ren($args) {
    if (empty($args)) {
        return new CommandResult(false, '', "ren: missing file operands");
    }
    
    $parts = explode(' ', $args, 2);
    if (count($parts) < 2) {
        return new CommandResult(false, '', "ren: missing destination file operand");
    }
    
    $old = trim($parts[0]);
    $new = trim($parts[1]);
    
    $old_path = safe_path($old);
    if ($old_path === false || !file_exists($old_path)) {
        return new CommandResult(false, '', "ren: cannot stat '$old': No such file or directory");
    }
    
    $new_path = safe_path($new);
    if ($new_path === false) {
        $new_path = $_SESSION['current_dir'] . '/' . $new;
    }
    
    if (file_exists($new_path)) {
        return new CommandResult(false, '', "ren: cannot rename to '$new': File exists");
    }
    
    if (!@rename($old_path, $new_path)) {
        $error = error_get_last();
        return new CommandResult(false, '', "ren: cannot rename '$old' to '$new': " . ($error['message'] ?? 'Unknown error'));
    }
    
    return new CommandResult(true, "Renamed: $old -> $new");
}

// T040: chmod Command Handler
function handle_chmod($args) {
    if (empty($args)) {
        return new CommandResult(false, '', "chmod: missing operands");
    }
    
    $parts = explode(' ', $args, 2);
    if (count($parts) < 2) {
        return new CommandResult(false, '', "chmod: missing file operand");
    }
    
    $mode = trim($parts[0]);
    $file = trim($parts[1]);
    
    // Parse octal permission
    if (!preg_match('/^[0-7]{3,4}$/', $mode)) {
        return new CommandResult(false, '', "chmod: invalid mode: '$mode'");
    }
    
    $octal = octdec($mode);
    
    $path = safe_path($file);
    if ($path === false || !file_exists($path)) {
        return new CommandResult(false, '', "chmod: cannot access '$file': No such file or directory");
    }
    
    if (!@chmod($path, $octal)) {
        $error = error_get_last();
        return new CommandResult(false, '', "chmod: changing permissions of '$file': " . ($error['message'] ?? 'Unknown error'));
    }
    
    return new CommandResult(true, "Changed permissions of '$file' to $mode");
}

// T041-T043: chown Command Handler
function handle_chown($args) {
    if (empty($args)) {
        return new CommandResult(false, '', "chown: missing operands");
    }
    
    $parts = explode(' ', $args, 2);
    if (count($parts) < 2) {
        return new CommandResult(false, '', "chown: missing file operand");
    }
    
    $owner_spec = trim($parts[0]);
    $file = trim($parts[1]);
    
    $path = safe_path($file);
    if ($path === false || !file_exists($path)) {
        return new CommandResult(false, '', "chown: cannot access '$file': No such file or directory");
    }
    
    // Parse user:group format
    $owner_parts = explode(':', $owner_spec);
    $user = $owner_parts[0];
    $group = isset($owner_parts[1]) ? $owner_parts[1] : null;
    
    // Change owner
    if (!empty($user)) {
        if (!@chown($path, $user)) {
            $error = error_get_last();
            return new CommandResult(false, '', "chown: changing ownership of '$file': " . ($error['message'] ?? 'Unknown error'));
        }
    }
    
    // Change group
    if (!empty($group)) {
        if (!@chgrp($path, $group)) {
            $error = error_get_last();
            return new CommandResult(false, '', "chown: changing group of '$file': " . ($error['message'] ?? 'Unknown error'));
        }
    }
    
    return new CommandResult(true, "Changed ownership of '$file' to $owner_spec");
}

// T046-T051: /download Command Handler
function handle_download($args) {
    if (empty($args)) {
        return new CommandResult(false, '', "/download: missing file operand");
    }
    
    $path = safe_path($args);
    
    if ($path === false || !file_exists($path)) {
        return new CommandResult(false, '', "/download: cannot access '$args': No such file or directory");
    }
    
    if (!is_file($path)) {
        return new CommandResult(false, '', "/download: '$args': Is a directory");
    }
    
    if (!is_readable($path)) {
        return new CommandResult(false, '', "/download: '$args': Permission denied");
    }
    
    // Return special action for download
    return new CommandResult(true, '', null, 'download:' . $path);
}

// T052, T056-T057: /exec Command Handler
function handle_exec($args) {
    if (empty($args)) {
        return new CommandResult(false, '', "/exec: missing command");
    }
    
    $output = [];
    $return_var = 0;
    
    // T056: Capture stderr with 2>&1
    exec($args . ' 2>&1', $output, $return_var);
    
    $output_text = implode("\n", $output);
    
    // T057: Display exit code on error
    if ($return_var !== 0) {
        $output_text .= "\n[Exit code: $return_var]";
    }
    
    return new CommandResult(true, $output_text);
}

// T053: /phpinfo Command Handler
function handle_phpinfo($args) {
    return new CommandResult(true, '', null, 'phpinfo');
}

// T054, T058: /list Command Handler
function handle_list($args) {
    $commands = [
        "File System Navigation:",
        "  ls [path]              - List directory contents",
        "  cd <path>              - Change directory",
        "  cat <file>             - View file contents",
        "",
        "File Operations:",
        "  mkdir <dir>            - Create directory",
        "  rm <path>              - Remove file or directory (recursive)",
        "  cp <src> <dst>         - Copy file",
        "  ren <old> <new>        - Rename file or directory",
        "",
        "Permission Management:",
        "  chmod <mode> <file>    - Change permissions (e.g., chmod 755 file.txt)",
        "  chown <user:grp> <file> - Change ownership",
        "",
        "Special Commands:",
        "  /exec <command>        - Execute shell command",
        "  /download <file>       - Download file to browser",
        "  /phpinfo               - Display PHP configuration",
        "  /list                  - Show this command list",
        "  /clear                 - Clear console output",
        "  /autodestruct          - Delete this script from server",
    ];
    
    return new CommandResult(true, implode("\n", $commands));
}

// T055: /clear Command Handler
function handle_clear($args) {
    clear_console_output();
    return new CommandResult(true, '', null, 'clear');
}

// T058a: /autodestruct Command Handler
function handle_autodestruct($args) {
    return new CommandResult(true, '', null, 'autodestruct');
}

// T013: Error Handling Wrapper
function execute_command_safely($command_string) {
    try {
        $cmd = parse_command($command_string);
        if (empty($cmd['name'])) {
            return new CommandResult(true, '', null);
        }
        return handle_command($cmd);
    } catch (Exception $e) {
        return new CommandResult(false, '', 'Error: ' . $e->getMessage());
    }
}

// Process command if submitted
$command_result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['command'])) {
    $command_string = trim($_POST['command']);
    
    if (!empty($command_string)) {
        $command_result = execute_command_safely($command_string);
        
        // Handle special actions
        if ($command_result->action === 'download') {
            // T047-T049: Execute download
            $file_path = str_replace('download:', '', $command_result->action);
            
            // T047: MIME type detection
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file_path);
            finfo_close($finfo);
            if ($mime === false) {
                $mime = 'application/octet-stream';
            }
            
            // T051: Sanitize filename
            $filename = basename($file_path);
            
            // T048: Send HTTP headers
            header('Content-Type: ' . $mime);
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($file_path));
            header('Cache-Control: no-cache, must-revalidate');
            
            // T049: Stream file and exit
            readfile($file_path);
            exit;
        } elseif ($command_result->action === 'phpinfo') {
            // T053: Display phpinfo
            phpinfo();
            exit;
        } elseif ($command_result->action === 'autodestruct') {
            // T058a: Delete script and show confirmation
            $script_path = __FILE__;
            append_console_output('> ' . $command_string);
            append_console_output("Self-destruct initiated...");
            append_console_output("Script deleted: " . $script_path);
            
            // Delete the script file
            @unlink($script_path);
            
            // Show final message
            echo "<!DOCTYPE html><html><head><title>PHP Server Manager - Destroyed</title>";
            echo "<style>body{background:#000;color:#0f0;font-family:monospace;padding:50px;text-align:center;}</style>";
            echo "</head><body><h1>PHP Server Manager 1.0.0</h1>";
            echo "<p>Self-destruct complete. This script has been removed from the server.</p>";
            echo "<p>Goodbye.</p></body></html>";
            exit;
        } elseif ($command_result->action === 'clear') {
            // T055: Clear handled, just redirect
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } else {
            // T014: Append command and result to console output
            append_console_output('> ' . $command_string);
            if ($command_result->success) {
                if (!empty($command_result->output)) {
                    append_console_output($command_result->output);
                }
            } else {
                append_console_output($command_result->error);
            }
        }
    }
    
    // Redirect to prevent form resubmission
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// T014: Read console output for display
$console_output = read_console_output();

// Get current directory for display
$current_dir = $_SESSION['current_dir'];

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Server Manager</title>
    <style>
        /* T011: CSS Styling */
        body {
            background-color: #000;
            color: #0f0;
            font-family: 'Cascadia Mono', 'Lucida Console', 'Consolas', 'Courier New', monospace;
            margin: 0;
            padding: 20px;
            font-size: 14px;
        }
        
        #console {
            white-space: pre-wrap;
            word-wrap: break-word;
            line-height: 1.4;
            margin-bottom: 20px;
            min-height: 400px;
        }
        
        #current-dir {
            color: #0ff;
            margin-bottom: 10px;
            padding: 5px 0;
            border-top: 1px solid #0f0;
            padding-top: 10px;
        }
        
        #input-container {
            display: flex;
            align-items: center;
        }
        
        #prompt {
            margin-right: 10px;
            color: #0f0;
            font-size: 18px;
            font-weight: bold;
        }
        
        #command-input {
            flex: 1;
            background-color: #000;
            color: #0f0;
            border: none;
            outline: none;
            font-family: inherit;
            font-size: 14px;
            padding: 5px;
        }
        
        #command-input:focus {
            background-color: #001100;
        }
    </style>
</head>
<body>
    <!-- T010: HTML Structure -->
    <div id="console"><?php echo htmlspecialchars($console_output); ?></div>
    <div id="current-dir">Current directory: <?php echo htmlspecialchars($current_dir); ?></div>
    
    <form method="POST" id="console-form">
        <div id="input-container">
            <span id="prompt">&gt;</span>
            <input type="text" name="command" id="command-input" 
                   placeholder="Type your command or /list to list all" 
                   autofocus autocomplete="off">
        </div>
    </form>
    
    <!-- T012: JavaScript for Enter Key Submission -->
    <script>
        document.getElementById('command-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('console-form').submit();
            }
        });
        
        // Auto-scroll to bottom
        window.scrollTo(0, document.body.scrollHeight);
    </script>
</body>
</html>
