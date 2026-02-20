# Implementation Plan: Docker Development/Testing Container

**Branch**: `001-docker-dev-container` | **Date**: 2025-01-21 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `/specs/001-docker-dev-container/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command. See `.specify/templates/plan-template.md` for the execution workflow.

## Summary

Create a self-contained Docker development/testing container for the PHP Server Manager that provides an instant, safe testing environment. The container will include Nginx with PHP-FPM, pre-populated test data (5 directory levels, 30-50 files of varying types and permissions), and support volume mounting for iterative development. The solution uses only native PHP, Nginx, and standard shell commands without external package managers, aligned with the project's zero-dependency constitution.

## Technical Context

**Language/Version**: PHP 8.2+ (latest stable from official Docker images), Bash (for entrypoint script)  
**Primary Dependencies**: Nginx (web server), PHP-FPM (PHP FastCGI Process Manager) - both from official repositories  
**Storage**: Container filesystem - ephemeral (recreated on each build), test data inline-generated via Dockerfile RUN commands  
**Testing**: Manual verification only (per constitution - no testing frameworks)  
**Target Platform**: Docker/container runtime (platform-agnostic - Linux, macOS, Windows with Docker Desktop)
**Project Type**: Container infrastructure (supporting single-file PHP web application)  
**Performance Goals**: Container start <10 seconds, PHP script response <2 seconds, minimal resource footprint  
**Constraints**: Zero external packages beyond OS-level (PHP, Nginx, PHP-FPM only), self-contained Dockerfile (no external scripts), stateless container design  
**Scale/Scope**: Development/testing only (not production), single container, 30-50 test files across 5 directory levels, support for 1-3 concurrent developer connections

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

### Initial Check (Pre-Phase 0)

#### Principle I: Working Code Over Quality
**Status**: ✅ COMPLIANT  
**Verification**: Container uses straightforward, functional approach - simple shell script entrypoint, direct Nginx/PHP-FPM configuration, inline test data generation. No over-engineering.

#### Principle II: Usability Over Performance
**Status**: ✅ COMPLIANT  
**Verification**: Focus is on developer ease of use - simple build/run commands, clear port mapping, volume mount support for iterative development. Performance is secondary (SC-001: <5min build, SC-002: <10s start are reasonable, not aggressive).

#### Principle III: Native Methods Only
**Status**: ✅ COMPLIANT  
**Verification**: Container uses only PHP (native language), Nginx/PHP-FPM (standard web stack), and shell commands (mkdir, echo, printf) for test data generation. No Composer, npm, or other package managers. FR-012 explicitly enforces this: "no external package managers or dependencies beyond what PHP provides natively."

#### Principle IV: No Testing Required
**Status**: ✅ COMPLIANT  
**Verification**: No test frameworks, no test infrastructure. Manual verification through browser access and feature testing. Feature spec acknowledges "Testing: Manual verification only."

#### GATE RESULT (Pre-Phase 0): ✅ PASS - All constitutional principles satisfied. Proceed to Phase 0.

---

### Post-Phase 1 Re-check

#### Principle I: Working Code Over Quality
**Status**: ✅ COMPLIANT (CONFIRMED)  
**Verification**: Design artifacts maintain simplicity:
- Entrypoint script: 5 lines (bash, no complex logic)
- Nginx config: Minimal (~30 lines, basic FastCGI setup)
- Test data generation: Inline shell commands (no external scripts)
- Data model: Straightforward entities (Container, Test Data, Processes)
- No abstractions, patterns, or frameworks beyond necessary infrastructure

**Post-Design Evidence**:
- `contracts/entrypoint-script.md`: Simple bash script, no supervisord
- `contracts/nginx-config.md`: Minimal configuration, no unnecessary directives
- `data-model.md`: Entity definitions focus on functionality, not purity

#### Principle II: Usability Over Performance
**Status**: ✅ COMPLIANT (CONFIRMED)  
**Verification**: Design prioritizes developer experience:
- Quickstart guide: 3-step setup (build, run, access)
- Volume mounting: Standard Docker `-v` flag (no custom tooling)
- Clear troubleshooting section with common issues
- Alpine base image chosen for usability (smaller downloads) not performance

**Post-Design Evidence**:
- `quickstart.md`: Comprehensive guide with examples, troubleshooting, expected outputs
- `data-model.md`: Volume Mount entity with 2-second sync latency (usability focused)
- Build time expectations documented: ~3-4 minutes first run (reasonable, not optimized)

#### Principle III: Native Methods Only
**Status**: ✅ COMPLIANT (CONFIRMED)  
**Verification**: Design uses only native/standard tools:
- PHP: Native (from official image)
- Nginx: OS-level package (not external dependency)
- Bash: Built-in shell
- Test data: Shell commands (mkdir, echo, dd, cat)
- No Composer, npm, pip, or any package managers for application dependencies

**Post-Design Evidence**:
- `research.md` Decision 3: "Uses only standard shell commands, no external scripts or tools"
- `contracts/nginx-config.md`: Only includes built-in Nginx modules
- `data-model.md`: Test data generated with dd, cat, heredoc (POSIX utilities)

#### Principle IV: No Testing Required
**Status**: ✅ COMPLIANT (CONFIRMED)  
**Verification**: Design explicitly avoids testing infrastructure:
- No test frameworks in contracts
- No CI/CD testing specifications
- Manual verification documented in quickstart and contracts

**Post-Design Evidence**:
- `contracts/entrypoint-script.md`: "Manual Verification (Per Constitution)" section
- `contracts/nginx-config.md`: "Testing Contract: No automated tests required"
- `quickstart.md`: Manual testing steps, no test automation

#### GATE RESULT (Post-Phase 1): ✅ PASS - All constitutional principles maintained through design phase. Proceed to Phase 2 (task generation).

---

### Compliance Summary

| Principle | Pre-Phase 0 | Post-Phase 1 | Notes |
|-----------|-------------|--------------|-------|
| I: Working Code Over Quality | ✅ PASS | ✅ PASS | Simplicity maintained throughout design |
| II: Usability Over Performance | ✅ PASS | ✅ PASS | Developer experience prioritized in all artifacts |
| III: Native Methods Only | ✅ PASS | ✅ PASS | Zero external dependencies confirmed in design |
| IV: No Testing Required | ✅ PASS | ✅ PASS | Manual verification specified, no test frameworks |

**Overall**: ✅ CONSTITUTIONAL COMPLIANCE VERIFIED - Implementation may proceed.

## Project Structure

### Documentation (this feature)

```text
specs/[###-feature]/
├── plan.md              # This file (/speckit.plan command output)
├── research.md          # Phase 0 output (/speckit.plan command)
├── data-model.md        # Phase 1 output (/speckit.plan command)
├── quickstart.md        # Phase 1 output (/speckit.plan command)
├── contracts/           # Phase 1 output (/speckit.plan command)
└── tasks.md             # Phase 2 output (/speckit.tasks command - NOT created by /speckit.plan)
```

### Source Code (repository root)

```text
# Single-file PHP project with container infrastructure
/
├── server-manager.php         # Main PHP application (existing)
├── Dockerfile                 # Container definition (NEW - Phase 1)
├── .dockerignore             # Docker build exclusions (NEW - Phase 1)
├── docker-entrypoint.sh      # Container startup script (NEW - Phase 1)
├── nginx-default.conf        # Nginx configuration (NEW - Phase 1)
└── README.md                 # Updated with container usage instructions (MODIFIED)

specs/001-docker-dev-container/
├── plan.md                   # This file
├── research.md               # Phase 0 output
├── data-model.md             # Phase 1 output
├── quickstart.md             # Phase 1 output (container usage guide)
└── contracts/                # Phase 1 output (Nginx config contract)
    └── nginx-config.md       # Nginx configuration specification
```

**Structure Decision**: Single-file PHP project with container support files at root. Container-related artifacts (Dockerfile, entrypoint script, nginx config) live alongside the main server-manager.php file for simplicity and discoverability. No src/ directory needed since this is a single-file application. Container configuration files are self-contained and reference each other through standard Docker mechanisms (COPY commands in Dockerfile).

## Complexity Tracking

> **Fill ONLY if Constitution Check has violations that must be justified**

**No violations** - Constitution Check passed all gates. No complexity justifications required.
