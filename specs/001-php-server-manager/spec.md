# Feature Specification: PHP Server Manager

**Feature Branch**: `001-php-server-manager`  
**Created**: 2025-01-24  
**Status**: Draft  
**Input**: User description: "Single PHP Script - PHP Server Manager - A single-page application in PHP where ALL HTML, CSS, and PHP code are in the same file. It can be uploaded to any server to immediately start managing it."

## Clarifications

### Session 2025-01-24

- Q: Console Output Persistence Strategy → A: Session-based cleanup — clear temporary file when PHP session expires (typically ~24 minutes of inactivity). This provides the best balance between usability and resource management.
- Q: Security & Access Control → A: Simple password variable at the top of the file. A single hardcoded password variable that the user sets before uploading. Minimal barrier while keeping single-file simplicity.
- Q: Error Handling for Failed Operations → A: Display PHP error messages directly in the console output for transparency and debuggability.
- Q: Directory Listing Format → A: Detailed listing with permissions, owner, size, modification date, and name — similar to `ls -la` output.
- Q: Dangerous Command Confirmation → A: Execute immediately, no confirmation required. The user is authenticated and expected to know what they're doing. Fast, direct console experience.
- Q: Console Output Log File Naming Strategy → A: Session ID-based filename (e.g., `console_output_{session_id}.txt`) for automatic isolation per user and easy cleanup.
- Q: Result Display Limits & Pagination → A: No limit — just show results when ready. No performance caps or pagination needed. Simply display all results once the filesystem operation completes.
- Q: File Upload from Client to Server → A: Out of scope — no upload feature. Maintain the current scope boundary. No file upload from client to server.

### Session 2026-02-19

- Q: Temp File Naming → A: Session ID-based filename (e.g., `console_output_{session_id}.txt`) for automatic per-user isolation and easy cleanup.
- Q: Max Response Time for `ls` → A: No limit — just show results when ready. No performance caps or pagination needed.
- Q: File Upload → A: Confirmed out of scope — no upload feature. Maintain current scope boundary.
- Q: Platform Target → A: Unix/Linux only — no Windows compatibility. No need to handle Windows paths or Windows-specific behavior. Remove any Windows references.
- Q: Server Process Crash Handling → A: Not applicable — the tool is a file/directory manager, not a process manager. This concern doesn't apply to the tool's scope.
- Q: Temporary file cleanup mechanism → A: PHP native session garbage collection (passive cleanup) — simplest approach, no custom cleanup code needed. Rely on PHP's built-in session GC mechanism.
- Q: Current directory state persistence strategy → A: PHP session storage via $_SESSION — store current directory in $_SESSION. More secure and consistent with the session-based architecture already in use.

### Session 2025-01-25

- Q: Session Timeout File Cleanup → A: PHP native session garbage collection (passive cleanup) — no custom cleanup code needed.
- Q: Current Directory State Persistence → A: PHP session storage via $_SESSION — store current directory in session, more secure and consistent with existing architecture.
- Q: `ls` Output Implementation → A: PHP native `scandir()`/`stat()` with text column alignment — most reliable, avoids shell injection, aligns with native-only constraint.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Basic File System Navigation (Priority: P1)

As a server administrator, I need to browse and navigate the file system through a web interface so I can quickly explore directory structures and locate files without SSH access.

**Why this priority**: This is the foundation of all file management operations. Without the ability to navigate directories and list contents, no other operations can be performed effectively. This represents the minimum viable product.

**Independent Test**: Can be fully tested by uploading the single PHP file to a server, accessing it via browser, using the `ls` command to view directory contents, and the `cd` command to navigate into subdirectories. The console should display the current directory and file listings, delivering immediate value for file exploration.

**Acceptance Scenarios**:

1. **Given** the PHP Server Manager is loaded in a browser, **When** the user types `ls` and presses Enter, **Then** the console displays the contents of the current directory with detailed information (permissions, owner, size, modification date, and name) similar to `ls -la` output format
2. **Given** the user is viewing a directory listing, **When** the user types `cd subdirectory` and presses Enter, **Then** the console shows the current directory has changed to the subdirectory and subsequent `ls` commands operate in that directory
3. **Given** the user has navigated into a subdirectory, **When** the user types `cd ..` and presses Enter, **Then** the console shows the current directory has moved up one level
4. **Given** the user has submitted multiple commands, **When** the page is refreshed or the form is submitted again, **Then** the console output persists and shows the command history until the PHP session expires
5. **Given** the user is in any directory, **When** the console displays output, **Then** the current working directory path is always visible at the bottom of the console output

---

### User Story 2 - File Content Viewing (Priority: P2)

As a server administrator, I need to view file contents directly in the console so I can quickly read configuration files, logs, and scripts without downloading them.

**Why this priority**: After navigation (P1), viewing file contents is the next most common operation. It provides immediate diagnostic value for troubleshooting and configuration review without requiring file downloads.

**Independent Test**: Can be fully tested by navigating to a directory, typing `cat filename.txt` to view a text file's contents displayed in the console. This provides standalone value for reading configuration files and logs.

**Acceptance Scenarios**:

1. **Given** the user is in a directory containing a text file, **When** the user types `cat filename.txt` and presses Enter, **Then** the console displays the complete contents of the file
2. **Given** the user types `cat` with a non-existent filename, **When** the command is submitted, **Then** the console displays an appropriate error message
3. **Given** the user attempts to view a binary file, **When** the user types `cat binaryfile.exe`, **Then** the console displays a warning or indicates the file is not suitable for text viewing

---

### User Story 3 - Directory and File Creation (Priority: P3)

As a server administrator, I need to create new directories and manage the file system structure so I can organize files and set up new project directories.

**Why this priority**: After viewing and navigating (P1, P2), the ability to modify the file system structure is needed for actual server management tasks.

**Independent Test**: Can be fully tested by typing `mkdir newdirectory` to create a directory, then using `ls` to confirm it exists. This provides standalone value for directory organization tasks.

**Acceptance Scenarios**:

1. **Given** the user is in a directory, **When** the user types `mkdir newdirectory` and presses Enter, **Then** the console confirms directory creation and subsequent `ls` commands show the new directory
2. **Given** the user attempts to create a directory that already exists, **When** the command is submitted, **Then** the console displays an appropriate error message
3. **Given** the user lacks permissions to create a directory, **When** the user types `mkdir restricted`, **Then** the console displays a permission error message

---

### User Story 4 - File and Directory Deletion (Priority: P3)

As a server administrator, I need to remove files and directories so I can clean up unnecessary files and manage disk space.

**Why this priority**: Deletion is a critical but potentially destructive operation, so it's prioritized after viewing and creation capabilities are established.

**Independent Test**: Can be fully tested by creating a test file/directory, then using `rm filename` or `rm -r directoryname` to remove it, and confirming with `ls` that it's gone.

**Acceptance Scenarios**:

1. **Given** the user is in a directory containing a file, **When** the user types `rm filename` and presses Enter, **Then** the file is immediately deleted without confirmation prompt and the console confirms deletion
2. **Given** the user needs to remove a directory with contents, **When** the user types `rm directoryname` with recursive flag, **Then** the directory and all its contents are immediately removed without confirmation
3. **Given** the user attempts to remove a non-existent file, **When** the command is submitted, **Then** the console displays PHP error message directly in the output

---

### User Story 5 - File Operations: Copy and Rename (Priority: P4)

As a server administrator, I need to copy and rename files so I can create backups and reorganize file structures.

**Why this priority**: These are common maintenance operations but less critical than viewing, creating, and deleting files.

**Independent Test**: Can be fully tested by using `cp source.txt destination.txt` to copy a file and `ren oldname.txt newname.txt` to rename a file, then confirming changes with `ls`.

**Acceptance Scenarios**:

1. **Given** the user is in a directory with a file, **When** the user types `cp source.txt destination.txt` and presses Enter, **Then** the console confirms the copy and both files appear in `ls` output
2. **Given** the user wants to rename a file, **When** the user types `ren oldname.txt newname.txt` and presses Enter, **Then** the console confirms the rename and `ls` shows only the new filename
3. **Given** the user attempts to copy to a destination that already exists, **When** the command is submitted, **Then** the console displays a warning or error message

---

### User Story 6 - Permission Management (Priority: P4)

As a server administrator, I need to change file permissions and ownership so I can properly secure files and resolve permission issues.

**Why this priority**: Permission management is important for security but requires existing files to operate on, making it dependent on earlier priorities.

**Independent Test**: Can be fully tested by using `chmod 755 filename.txt` to change permissions and `chown user:group filename.txt` to change ownership, with visual confirmation in `ls` output.

**Acceptance Scenarios**:

1. **Given** the user is in a directory with a file, **When** the user types `chmod 755 filename.txt` and presses Enter, **Then** the console confirms the permission change
2. **Given** the user needs to change file ownership, **When** the user types `chown user:group filename.txt` and presses Enter, **Then** the console confirms the ownership change
3. **Given** the user lacks permissions to modify a file, **When** the command is submitted, **Then** the console displays a permission error message

---

### User Story 7 - File Download (Priority: P4)

As a server administrator, I need to download files from the server to my local machine so I can back up files or work with them locally.

**Why this priority**: Download functionality is important for backup and local editing but is an auxiliary feature compared to in-console operations.

**Independent Test**: Can be fully tested by typing `/download filename.txt`, which triggers a browser download dialog for the specified file.

**Acceptance Scenarios**:

1. **Given** the user is in a directory with files, **When** the user types `/download filename.txt` and presses Enter, **Then** the browser initiates a download of the file with the correct filename and content
2. **Given** the user attempts to download a non-existent file, **When** the command is submitted, **Then** the console displays an appropriate error message
3. **Given** the user attempts to download a large file, **When** the download command is executed, **Then** the file downloads completely without corruption

---

### User Story 8 - Command Execution and System Information (Priority: P5)

As a server administrator, I need to execute arbitrary commands and view system information so I can perform advanced diagnostics and custom operations.

**Why this priority**: These are advanced features for power users that depend on all basic file operations being in place first. They provide extended functionality beyond core file management.

**Independent Test**: Can be fully tested by typing `/exec ls -la` to execute a command, `/phpinfo` to view PHP configuration, and `/list` to see all available commands. Each provides standalone diagnostic value.

**Acceptance Scenarios**:

1. **Given** the user needs to run a custom command, **When** the user types `/exec whoami` and presses Enter, **Then** the console displays the output of the executed command
2. **Given** the user wants to view PHP configuration, **When** the user types `/phpinfo` and presses Enter, **Then** the console displays the complete phpinfo() output
3. **Given** the user is unsure what commands are available, **When** the user types `/list` and presses Enter, **Then** the console displays all available commands with brief descriptions
4. **Given** the user has accumulated console output, **When** the user types `/clear` and presses Enter, **Then** the console output is cleared, showing only the application header and current directory

---

### Edge Cases

- What happens when the user navigates to a directory they don't have permission to read? → PHP error message displayed directly in console
- How does the system handle commands with invalid syntax or missing required arguments? → PHP error message displayed directly in console
- What happens when the temporary file storing console output becomes very large (thousands of lines)? → Automatically cleared when session expires (~24 minutes inactivity)
- How does the system behave when the user attempts to navigate to a non-existent directory with `cd`? → PHP error message displayed directly in console
- What happens if the temporary file storage location is not writable? → PHP error message displayed directly in console
- How does the system handle file operations on files with special characters or spaces in their names? → Tested with PHP's file handling functions
- What happens when the user submits an empty command? → Ignored or displays empty output line
- How does the system handle simultaneous requests from multiple browser sessions? → Each session has independent state and temporary file
- What happens if a command takes a very long time to execute? → Limited by PHP's max_execution_time setting
- How does the system handle attempts to remove or modify the PHP Server Manager script itself? → Executes immediately without confirmation; user is responsible for consequences
- What happens when the user session expires? → Console output temporary file is cleaned up; user must re-authenticate with password
- How does the system handle incorrect password attempts? → Authentication failure message displayed; no access to console interface

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST be contained in a single PHP file with all HTML, CSS, and PHP code included
- **FR-002**: System MUST display a console-style interface with black background and monospace font ('Cascadia Mono', 'Lucida Console', 'Consolas', 'Courier New')
- **FR-003**: System MUST display "PHP Server Manager 1.0.0" as the first line of console output on every page load
- **FR-004**: System MUST provide an input field at the bottom of the page preceded by the ">" character
- **FR-005**: System MUST submit commands via POST when the user presses Enter in the input field
- **FR-006**: System MUST persist console output across POST requests using temporary file storage with session ID-based filenames (e.g., `console_output_{session_id}.txt`), providing automatic isolation per user; cleanup relies on PHP's native session garbage collection mechanism (passive cleanup) with no custom cleanup code required
- **FR-007**: System MUST persist current directory state across POST requests using PHP session storage ($_SESSION), providing security and consistency with the session-based architecture
- **FR-008**: System MUST display the current working directory path at the bottom of the console output
- **FR-009**: System MUST support the `ls` command to list directory contents in the current directory with detailed information including permissions, owner, size, modification date, and name (similar to `ls -la` output format), displaying all results without pagination or response time limits; implementation MUST use PHP native `scandir()` and `stat()` functions with text column alignment to avoid shell injection risks and maintain native-only constraint
- **FR-010**: System MUST support the `cd` command to navigate between directories
- **FR-011**: System MUST support the `cat` command to display file contents in the console
- **FR-012**: System MUST support the `mkdir` command to create new directories
- **FR-013**: System MUST support the `rm` command to remove files and directories recursively
- **FR-014**: System MUST support the `cp` command to copy files
- **FR-015**: System MUST support the `ren` command to rename files and directories
- **FR-016**: System MUST support the `chmod` command to change file permissions
- **FR-017**: System MUST support the `chown` command to change file ownership
- **FR-018**: System MUST support the `/list` command to display all available commands
- **FR-019**: System MUST support the `/clear` command to clear console output
- **FR-020**: System MUST support the `/exec` command to execute arbitrary shell commands using PHP's exec() function
- **FR-021**: System MUST support the `/download` command to initiate browser download of specified files
- **FR-022**: System MUST support the `/phpinfo` command to display PHP configuration information
- **FR-023**: System MUST display PHP error messages directly in the console output when commands fail or receive invalid arguments, providing transparency and debuggability
- **FR-024**: System MUST use only native PHP functions without external dependencies or packages
- **FR-025**: System MUST display input field placeholder text: "Type your command or /list to list all"
- **FR-026**: System MUST require password authentication using a hardcoded password variable defined at the top of the PHP file before allowing access to the console interface
- **FR-027**: System MUST execute all commands immediately without confirmation prompts, including potentially destructive operations like `rm` and `chmod`

### Key Entities

- **Command**: Represents a user-entered instruction consisting of a command name (e.g., "ls", "cd", "/exec") and optional arguments
- **Console Output**: The accumulated text display showing command history and results, persisted to a temporary file
- **Current Directory**: The active file system path context for executing file operations, persisted across requests
- **File System Entry**: Represents a file or directory with attributes such as name, path, size, permissions, and ownership

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Users can upload the single PHP file to any server, set the password variable, and immediately access a functional file management interface after authentication
- **SC-002**: Users can navigate the complete file system hierarchy using cd and ls commands with instant visual feedback, with directory listings showing detailed information (permissions, owner, size, modification date, name)
- **SC-003**: All 13 supported commands execute successfully and display appropriate output or PHP error messages within 2 seconds for typical operations
- **SC-004**: Console output persists across page refreshes and form submissions, with automatic cleanup handled by PHP's native session garbage collection mechanism when the PHP session expires
- **SC-005**: The current directory context is maintained correctly across all command submissions throughout a user session
- **SC-006**: File download operations successfully transfer files to the user's local machine without corruption
- **SC-007**: The console interface is immediately recognizable as a terminal-style interface with consistent monospace font rendering across major browsers
- **SC-008**: Users can clear accumulated console output and start fresh using the /clear command
- **SC-009**: Users can discover all available commands using the /list command without referring to external documentation
- **SC-010**: The application functions without requiring any external PHP packages, libraries, or dependencies
- **SC-011**: Destructive operations (rm, chmod, chown) execute immediately without confirmation prompts, providing fast direct console experience for authenticated users

## Assumptions

- The server is running on Unix/Linux platform with PHP 5.4 or higher installed with session support enabled
- The server allows PHP's exec() function (not disabled in php.ini) for the /exec command to function
- The server has a writable temporary directory for storing console output (typically /tmp on Unix/Linux)
- PHP sessions are configured with reasonable garbage collection settings (typically 24 minutes timeout); session garbage collection will automatically clean up temporary console output files when sessions expire (passive cleanup with no custom cleanup code required)
- The user accessing the application has basic familiarity with Unix/Linux command-line syntax
- The server allows the necessary file system operations (chmod, chown, file reading/writing) based on the web server's user permissions
- The PHP script has at minimum read access to the directory where it is placed
- Browser JavaScript is enabled for form submission behavior (though basic HTML form submission will work without it)
- Users understand that this is a powerful tool that provides direct file system access and should be secured appropriately
- Authenticated users are trusted administrators who understand the implications of destructive commands
- File operations are performed in the context of the web server's user account (typically www-data, apache, or nginx)
- The user will configure the password variable before deploying the script to a server

## Constraints

- Single file architecture: All HTML, CSS, and PHP code must be in one file with no external dependencies
- No external packages: Only native PHP functions and built-in HTML/CSS may be used
- No testing required: The pragmatic approach prioritizes working code over comprehensive test coverage
- Working code over quality: Practical solutions are preferred over elegant architectural patterns
- Usability over performance: User experience takes priority over optimization
- Platform target is Unix/Linux only — no Windows compatibility required; no need to handle Windows-specific paths or behaviors
- The script operates with the permissions of the web server process, which may restrict certain file system operations
- Console output storage is limited by available temporary file storage space, with automatic cleanup tied to PHP session expiration
- Password protection uses a single hardcoded variable, providing minimal security suitable for trusted environments only
- Command execution time is limited by PHP's max_execution_time setting
- Directory listing operations (`ls`) display all results without pagination or imposed response time limits

## Security Considerations

**Authentication**: The application uses a simple hardcoded password variable defined at the top of the PHP file. Users must set this password before uploading the script to the server. This provides a minimal security barrier while maintaining single-file simplicity.

**Security implications**:

- The application provides powerful file system access and requires password authentication before granting access
- The /exec command allows arbitrary code execution and represents a significant security risk if exposed to untrusted users
- File operations are limited by the web server user's permissions
- The simple password authentication is minimal protection; additional access controls (e.g., IP whitelisting, HTTPS) are recommended for production environments
- The application should be removed from production servers after use or placed behind additional security layers
- Commands execute immediately without confirmation, assuming authenticated users understand the risks of destructive operations

## Out of Scope

The following are explicitly excluded from this feature:

- File upload functionality (from client to server) — confirmed not in scope
- File editing capabilities (modifying file contents)
- Windows platform support — this tool is designed for Unix/Linux systems only
- Server process management — this tool manages files and directories, not server processes
- Multiple user authentication or authorization system
- Visual file browser or GUI-based file manager
- Syntax highlighting for code files
- Search functionality across files or directories
- Batch operations or command scripting
- Real-time command output streaming
- Command history navigation (up/down arrow keys)
- Tab completion for file names
- Undo/redo functionality for destructive operations
- File preview for images or media files
- Compressed file handling (zip, tar, etc.)
- Text editor integration
- File comparison or diff capabilities
