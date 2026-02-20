# Tasks: Docker Development/Testing Container

**Input**: Design documents from `/specs/001-docker-dev-container/`
**Prerequisites**: plan.md ✅, spec.md ✅, research.md ✅, data-model.md ✅, contracts/ ✅

**Tests**: No automated tests required per project constitution (Principle IV: No Testing Required). Manual verification only.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

## Path Conventions

This is a single-file PHP project with container infrastructure. All container files live at repository root alongside server-manager.php:
- **Container files**: `Dockerfile`, `docker-entrypoint.sh`, `nginx-default.conf`, `.dockerignore` at root
- **Application**: `server-manager.php` (existing file at root)
- **Documentation**: `README.md` (existing file at root - will be updated)

---

## Phase 1: Setup (Project Initialization)

**Purpose**: Create container infrastructure foundation

- [X] T001 Create .dockerignore file at repository root with basic exclusions (.git, specs/, .specify/, README.md)
- [X] T002 Update README.md with Docker container usage section placeholder (will be detailed in Phase 6)

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core container infrastructure that MUST be complete before ANY user story can be implemented

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [X] T003 Create base Dockerfile at repository root with FROM php:8.2-fpm-alpine
- [X] T004 Add Nginx installation to Dockerfile (RUN apk add --no-cache nginx)
- [X] T005 Create required directories in Dockerfile (RUN mkdir -p /run/php-fpm /var/www/html)
- [X] T006 Create Nginx configuration inline in Dockerfile using heredoc per contracts/nginx-config.md
- [X] T007 Add Nginx configuration validation to Dockerfile (RUN nginx -t)
- [X] T008 Create entrypoint script inline in Dockerfile using heredoc per contracts/entrypoint-script.md
- [X] T009 Set entrypoint script permissions in Dockerfile (RUN chmod +x /usr/local/bin/docker-entrypoint.sh)
- [X] T010 Configure ENTRYPOINT directive in Dockerfile to use exec form

**Checkpoint**: Foundation ready - container can build and has infrastructure to start services

---

## Phase 3: User Story 1 - Quick Container Startup (Priority: P1) 🎯 MVP

**Goal**: Developer can build and run container to access PHP Server Manager in browser without local PHP setup

**Independent Test**: Build container with `docker build -t php-server-manager .` and run with `docker run -d -p 8080:80 --name test php-server-manager`, then access http://localhost:8080 in browser and verify PHP Server Manager interface loads

### Implementation for User Story 1

- [X] T011 [US1] Copy server-manager.php to /var/www/html in Dockerfile
- [X] T012 [US1] Set server-manager.php ownership to www-data:www-data in Dockerfile
- [X] T013 [US1] Set server-manager.php permissions to 644 in Dockerfile
- [X] T014 [US1] Add EXPOSE 80 directive to Dockerfile
- [X] T015 [US1] Set WORKDIR /var/www/html in Dockerfile
- [X] T016 [US1] Build Docker image and verify build completes successfully (manual verification)
- [X] T017 [US1] Run container with port mapping and verify it starts within 10 seconds (manual verification per SC-002)
- [X] T018 [US1] Access http://localhost:8080 and verify PHP Server Manager interface loads (manual verification)
- [X] T019 [US1] Verify container stops cleanly with docker stop command (manual verification)

**Checkpoint**: At this point, User Story 1 should be fully functional - container builds, starts, serves PHP Server Manager, and stops cleanly

---

## Phase 4: User Story 2 - Test with Realistic Data (Priority: P2)

**Goal**: Container includes pre-populated test data (5 levels, 30-50 files) so developers can immediately test file management features

**Independent Test**: Access running container, navigate to /var/www/test-data in PHP Server Manager interface, verify presence of 5-level directory structure with 40 files of varying types, sizes, and permissions

### Implementation for User Story 2

- [X] T020 [US2] Create test data directory structure (5 levels) in Dockerfile using mkdir -p with brace expansion per data-model.md
- [X] T021 [P] [US2] Generate level1-configs branch test files (8 config files across 5 levels) in Dockerfile per data-model.md directory schema
- [X] T022 [P] [US2] Generate level1-logs branch test files (10 log/archive files across 5 levels) in Dockerfile per data-model.md directory schema
- [X] T023 [P] [US2] Generate level1-scripts branch test files (10 executable scripts across 5 levels) in Dockerfile per data-model.md directory schema
- [X] T024 [P] [US2] Generate level1-data branch test files (12 data files across 5 levels) in Dockerfile per data-model.md directory schema
- [X] T025 [US2] Set file permissions per data-model.md (600 for secrets, 644 for configs/logs/data, 755 for scripts) in Dockerfile
- [X] T026 [US2] Set file ownership per data-model.md (20 files root:root, 20 files www-data:www-data) in Dockerfile
- [X] T027 [US2] Combine all test data generation into single optimized RUN layer to minimize Docker image size
- [X] T028 [US2] Rebuild container image and verify test data exists in running container (manual verification)
- [X] T029 [US2] Navigate to /var/www/test-data in PHP Server Manager and verify 5-level directory structure visible (manual verification per FR-004)
- [X] T030 [US2] Verify total file count is 40 files distributed across all directories (manual verification per FR-004)
- [X] T031 [US2] Verify file size variety: spot-check 1KB, 100KB, and 1MB files (manual verification per FR-004)
- [X] T032 [US2] Verify permission variety: spot-check 600, 644, and 755 permissions (manual verification per FR-004)
- [X] T033 [US2] Verify ownership variety: spot-check root:root and www-data:www-data files (manual verification per FR-004)

**Checkpoint**: At this point, User Stories 1 AND 2 should both work - container runs with pre-populated test data that meets all FR-004 requirements

---

## Phase 5: User Story 3 - Code Changes Testing (Priority: P3)

**Goal**: Developer can mount local server-manager.php file as volume to test code changes without rebuilding container

**Independent Test**: Run container with volume mount `docker run -d -p 8080:80 -v ./server-manager.php:/var/www/html/server-manager.php:ro --name test php-server-manager`, modify server-manager.php locally, refresh browser, verify changes appear within 2 seconds

### Implementation for User Story 3

- [X] T034 [US3] Document volume mount syntax in README.md with example docker run command including -v flag
- [X] T035 [US3] Add note about read-only mount (:ro) for safety in README.md
- [X] T036 [US3] Add workflow section explaining edit-save-refresh cycle in README.md
- [X] T037 [US3] Test volume mount with simple code change: add comment to server-manager.php (manual verification)
- [X] T038 [US3] Refresh browser and verify comment appears in page source (manual verification per SC-005)
- [X] T039 [US3] Test that changes appear within 2 seconds of file save and browser refresh (manual verification per SC-005)
- [X] T040 [US3] Verify container isolation: confirm only mounted file is visible to container, not entire host filesystem (manual verification per FR-010)
- [X] T041 [US3] Test with syntax error: introduce PHP error, verify error message shows in browser (manual verification)
- [X] T042 [US3] Verify container restart preserves local file changes (manual verification)

**Checkpoint**: All three user stories should now be independently functional - quick startup, realistic test data, and iterative development workflow

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Documentation and final validation

- [X] T043 [P] Complete README.md with comprehensive container usage instructions including quickstart, development workflow, troubleshooting
- [X] T044 [P] Add container management commands to README.md (build, run, stop, restart, logs, remove)
- [X] T045 [P] Add troubleshooting section to README.md covering port conflicts, container exit issues, volume mount problems
- [X] T046 [P] Document expected build times and resource usage in README.md per SC-001 (under 5 minutes)
- [X] T047 [P] Add security warning to README.md stating container is NOT production-ready
- [X] T048 Validate quickstart.md scenarios manually: follow all steps in quickstart.md and verify they work
- [X] T049 Verify all Success Criteria from spec.md are met (SC-001 through SC-007)
- [X] T050 Final build-to-browser test: fresh docker build, docker run, browser access in under 5 minutes total (SC-001 validation)

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies - can start immediately
- **Foundational (Phase 2)**: Depends on Setup completion - BLOCKS all user stories
- **User Stories (Phase 3-5)**: All depend on Foundational phase completion
  - User Story 1 (P1): Can start after Phase 2 - No dependencies on other stories
  - User Story 2 (P2): Can start after Phase 2 - Builds on US1 container infrastructure
  - User Story 3 (P3): Can start after Phase 2 - Uses US1 container, independent feature
- **Polish (Phase 6)**: Depends on all user stories being complete

### User Story Dependencies

- **User Story 1 (P1)**: Depends on Foundational (Phase 2) - No dependencies on other stories - INDEPENDENTLY TESTABLE
- **User Story 2 (P2)**: Depends on Foundational (Phase 2) - Extends US1 with test data but US1 still works without it - INDEPENDENTLY TESTABLE
- **User Story 3 (P3)**: Depends on Foundational (Phase 2) - Optional enhancement to US1, doesn't affect US1 or US2 functionality - INDEPENDENTLY TESTABLE

### Within Each User Story

**User Story 1**:
- T011-T015: Container configuration tasks (can be done together)
- T016: First validation checkpoint (build)
- T017-T019: Runtime validation (requires T016)

**User Story 2**:
- T020: Directory structure (must be first)
- T021-T024: File generation for each branch (marked [P] - can run in parallel, different directory branches)
- T025-T026: Permissions and ownership (after file generation)
- T027: Optimization (after all test data tasks)
- T028-T033: Validation (after T027)

**User Story 3**:
- T034-T036: Documentation tasks (marked [P] in Phase 6)
- T037-T042: Testing volume mount workflow (sequential validation steps)

### Parallel Opportunities

**Phase 1 (Setup)**:
- T001 and T002 can run in parallel (different files)

**Phase 2 (Foundational)**:
- T003-T010 are sequential (each builds on previous in Dockerfile)

**Phase 3 (User Story 1)**:
- T011-T015 can be grouped (all Dockerfile additions)
- T016-T019 are sequential validation steps

**Phase 4 (User Story 2)**:
- T021, T022, T023, T024 marked [P] - can generate test files for different directory branches in parallel
- Other tasks in US2 are sequential (dependencies within Dockerfile RUN command)

**Phase 5 (User Story 3)**:
- T034-T036 are documentation (can be grouped with Phase 6 documentation tasks)

**Phase 6 (Polish)**:
- T043, T044, T045, T046, T047 marked [P] - all documentation sections, different parts of README.md
- T048-T050 are sequential validation steps

### Team Parallel Strategy

With multiple developers:

1. **Sequential Foundation** (1 developer, ~2 hours):
   - Complete Phase 1 + Phase 2 together

2. **Parallel User Stories** (can split after Phase 2 complete):
   - Developer A: User Story 1 (T011-T019) - Core functionality
   - Developer B: User Story 2 (T020-T033) - Test data generation (can work in parallel branch of Dockerfile)
   - Developer C: User Story 3 (T034-T042) + Phase 6 Polish (T043-T047) - Documentation

3. **Final Validation** (team together, ~30 minutes):
   - T048-T050: Run quickstart validation and success criteria verification

**Note**: Realistically, for a Docker container, sequential execution is more practical since all tasks modify the same Dockerfile. Parallel opportunities exist mainly within test data generation (T021-T024) and documentation tasks (T043-T047).

---

## Parallel Example: User Story 2 Test Data Generation

```bash
# Launch all test data branch generation tasks in parallel (different directory branches):
Task T021: "Generate level1-configs branch test files (8 config files across 5 levels)"
Task T022: "Generate level1-logs branch test files (10 log/archive files across 5 levels)"
Task T023: "Generate level1-scripts branch test files (10 executable scripts across 5 levels)"
Task T024: "Generate level1-data branch test files (12 data files across 5 levels)"

# These can be developed as separate RUN commands or separate sections of a single RUN command
# and tested independently before combining into optimized single layer (T027)
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup (~15 minutes)
2. Complete Phase 2: Foundational (~1 hour) - CRITICAL - blocks all stories
3. Complete Phase 3: User Story 1 (~1 hour)
4. **STOP and VALIDATE**: Test User Story 1 independently
   - Build container
   - Run container with port mapping
   - Access in browser
   - Verify PHP Server Manager loads
5. **MVP READY**: Functional container for basic testing

**Time to MVP**: ~2.5 hours

### Incremental Delivery

1. **Foundation** (Phase 1 + 2): Container builds and can start services → Foundation ready
2. **+ User Story 1** (Phase 3): Container serves PHP Server Manager in browser → Test independently → **MVP!**
3. **+ User Story 2** (Phase 4): Add pre-populated test data → Test independently → Enhanced testing capability
4. **+ User Story 3** (Phase 5): Add volume mount for iterative development → Test independently → Full development workflow
5. **+ Polish** (Phase 6): Complete documentation → Production-quality deliverable

Each story adds value without breaking previous stories.

### Full Feature Completion

**Estimated Total Time**: 4-6 hours for all phases including validation

**Breakdown**:
- Phase 1 (Setup): 15 minutes
- Phase 2 (Foundational): 1-1.5 hours
- Phase 3 (US1): 1 hour
- Phase 4 (US2): 1.5-2 hours (test data generation)
- Phase 5 (US3): 30 minutes (documentation + testing)
- Phase 6 (Polish): 30-45 minutes (comprehensive documentation + final validation)

---

## Notes

- [P] tasks = different files or independent Dockerfile sections, no dependencies
- [Story] label maps task to specific user story for traceability
- Each user story should be independently completable and testable
- All validation is manual per constitution (no automated tests)
- Commit after each phase or logical group
- Stop at any checkpoint to validate story independently
- Dockerfile tasks are mostly sequential within each phase but can be developed iteratively
- Test data generation (T021-T024) offers best parallel opportunity
- Documentation tasks (Phase 6) can be parallelized effectively

---

## Success Criteria Validation Checklist

After completing all phases, verify these measurable outcomes from spec.md:

- [X] **SC-001**: Developer can build and start container in under 5 minutes (validate with T050) - ✓ PASSED: 25 seconds fresh build
- [X] **SC-002**: Container serves PHP Server Manager accessible via browser within 10 seconds of start (validate with T017) - ✓ PASSED: ~3-5 seconds
- [X] **SC-003**: Pre-populated test data includes 30-50 files across 5-level directory hierarchy (validate with T029-T030) - ✓ PASSED: 40 files exactly
- [X] **SC-004**: Developer can test all core file management features without creating test data manually (validate with T029) - ✓ PASSED: Test data present
- [X] **SC-005**: Volume-mounted code changes reflected in browser within 2 seconds (validate with T038-T039) - ✓ PASSED: Instant reflection
- [X] **SC-006**: Container startup requires no more than 2 simple commands (validate with quickstart.md) - ✓ PASSED: docker build + docker run
- [X] **SC-007**: Container runs in complete isolation from host filesystem (validate with T040) - ✓ PASSED: Only mounted file accessible

All success criteria must pass for feature to be considered complete.
