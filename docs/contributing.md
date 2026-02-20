# Contributing

Contributions to PHP Server Manager are welcome.

## Guidelines

### Single-File Constraint

The application must remain a single PHP file (`server-manager.php`) with no external dependencies. All PHP, HTML, CSS, and JavaScript lives in this one file.

### Native PHP Only

Use only built-in PHP functions. Do not introduce Composer packages, npm modules, or any external libraries.

### PHP Version Compatibility

Maintain compatibility with PHP 5.4 and above. Do not use features introduced in later PHP versions without a fallback.

### Platform

The application targets Unix/Linux systems. Windows compatibility is explicitly out of scope.

## Development Setup

### Option A: Docker (Recommended)

See the [Docker Guide](docker.md) for setting up a containerized development environment with test data.

### Option B: PHP Built-in Server

```bash
php -S localhost:8000 server-manager.php
```

Access at `http://localhost:8000/server-manager.php`.

## Making Changes

1. Fork the repository.
2. Create a feature branch.
3. Make your changes to `server-manager.php`.
4. Test manually against a running instance (Docker or PHP built-in server).
5. Submit a pull request with a clear description of the change.

## Code Style

- Follow the existing code structure and naming conventions.
- Keep handler functions consistent in signature and return type (`CommandResult`).
- Use descriptive error messages that help the user understand what went wrong.
- Escape all output with `htmlspecialchars()` when rendering in the console.

## What's in Scope

- New commands that fit the server management use case
- Bug fixes
- Security improvements
- Documentation improvements

## What's Out of Scope

- File upload functionality
- File editing (inline text editor)
- Multi-file architecture
- External dependencies
- Windows support

## License

By contributing, you agree that your contributions will be licensed under the [MIT License](../LICENSE).
