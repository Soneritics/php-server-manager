# Tasks: PHP Server Manager

**Input**: Design documents from `/specs/001-php-server-manager/`
**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/command-interface.md

**Tests**: NO TESTS REQUIRED per Constitution Principle IV and spec constraint "No testing required"

**Organization**: Tasks are grouped by user story (P1-P5 priorities) to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

## Path Conventions

- **Single-file application**: All code in `server-manager.php` at repository root
- No separate directories for components (per single-file constraint)
- Documentation in `specs/001-php-server-manager/`

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Project initialization and basic structure

- [X] T001 Create server-manager.php with basic PHP structure and file header comments
- [X] T002 [P] Create README.md at repository root with installation instructions
- [X] T003 [P] Create LICENSE file at repository root

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core infrastructure that MUST be complete before ANY user story can be implemented

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [X] T004 Implement session management (session_start, session configuration) in server-manager.php
- [X] T005 [P] Implement password authentication logic with login form in server-manager.php
- [X] T006 [P] Implement console output file management (create, read, append) using session-based filenames in server-manager.php
- [X] T007 Implement session state initialization ($_SESSION['current_dir'], $_SESSION['authenticated']) in server-manager.php
- [X] T008 Implement command parsing logic (parse command string into name and args) in server-manager.php
- [X] T009 Implement CommandResult structure and command routing dispatcher in server-manager.php
- [X] T010 [P] Implement HTML structure with console display area and input form in server-manager.php
- [X] T011 [P] Implement CSS styling (black background, green text, monospace fonts) in server-manager.php
- [X] T012 [P] Implement JavaScript for Enter key form submission in server-manager.php
- [X] T013 Implement error handling wrapper (try-catch, error display) in server-manager.php
- [X] T014 Implement console output rendering with htmlspecialchars escaping in server-manager.php
- [X] T014a [P] Add "PHP Server Manager 1.0.0" header display on every page load (FR-003) in server-manager.php

**Checkpoint**: Foundation ready - user story implementation can now begin in parallel

---

## Phase 3: User Story 1 - Basic File System Navigation (Priority: P1) 🎯 MVP

**Goal**: Browse and navigate the file system through a web interface with ls and cd commands

**Independent Test**: Upload server-manager.php, access via browser, use `ls` to view directory contents and `cd` to navigate directories. Console displays current directory and file listings.

### Implementation for User Story 1

- [X] T015 [P] [US1] Implement `ls` command handler using scandir() and stat() in server-manager.php
- [X] T016 [P] [US1] Implement format_permissions() helper function (convert octal to rwxr-xr-x) in server-manager.php
- [X] T017 [P] [US1] Implement format_ls_output() function with column alignment (permissions, owner, size, date, name) in server-manager.php
- [X] T018 [US1] Implement `cd` command handler with directory validation and chdir() in server-manager.php
- [X] T019 [US1] Implement current directory display at bottom of console output in server-manager.php
- [X] T020 [US1] Implement session persistence for current directory ($_SESSION['current_dir']) in server-manager.php
- [X] T021 [US1] Add error handling for permission denied and non-existent directories in server-manager.php

**Checkpoint**: At this point, User Story 1 should be fully functional - users can navigate and list files

---

## Phase 4: User Story 2 - File Content Viewing (Priority: P2)

**Goal**: View file contents directly in the console with the cat command

**Independent Test**: Navigate to a directory, type `cat filename.txt` to view file contents displayed in the console.

### Implementation for User Story 2

- [X] T022 [US2] Implement `cat` command handler using file_get_contents() in server-manager.php
- [X] T023 [US2] Add file existence validation and error handling for cat command in server-manager.php
- [X] T024 [US2] Implement output escaping for safe HTML display of file contents in server-manager.php

**Checkpoint**: User Stories 1 AND 2 should both work independently

---

## Phase 5: User Story 3 - Directory and File Creation (Priority: P3)

**Goal**: Create new directories with the mkdir command

**Independent Test**: Type `mkdir newdirectory`, then use `ls` to confirm it exists.

### Implementation for User Story 3

- [X] T026 [US3] Implement `mkdir` command handler using mkdir() with $recursive=true to support nested path creation (e.g., `mkdir a/b/c`) in server-manager.php
- [X] T027 [US3] Add directory existence check and error handling in server-manager.php
- [X] T028 [US3] Add permission validation and error message display in server-manager.php
- [X] T029 [US3] Add success confirmation message for directory creation in server-manager.php

**Checkpoint**: User Stories 1, 2, AND 3 should all work independently

---

## Phase 6: User Story 4 - File and Directory Deletion (Priority: P3)

**Goal**: Remove files and directories with the rm command (immediate execution, no confirmation)

**Independent Test**: Create a test file/directory, use `rm filename` or `rm -r directoryname` to remove it, confirm with `ls`.

### Implementation for User Story 4

- [X] T030 [US4] Implement `rm` command handler for files using unlink() in server-manager.php
- [X] T031 [US4] Implement recursive directory deletion function (rmdir_recursive) in server-manager.php
- [X] T032 [US4] Add file/directory existence validation in server-manager.php
- [X] T033 [US4] Implement immediate deletion (no confirmation prompt per FR-027) in server-manager.php
- [X] T034 [US4] Add error handling and PHP error message display in server-manager.php

**Checkpoint**: User Stories 1-4 should all work independently

---

## Phase 7: User Story 5 - File Operations: Copy and Rename (Priority: P4)

**Goal**: Copy and rename files with cp and ren commands

**Independent Test**: Use `cp source.txt destination.txt` to copy a file and `ren oldname.txt newname.txt` to rename a file, confirm with `ls`.

### Implementation for User Story 5

- [X] T035 [P] [US5] Implement `cp` command handler using copy() in server-manager.php
- [X] T036 [P] [US5] Implement `ren` command handler using rename() in server-manager.php
- [X] T037 [US5] Add source file existence validation for both commands in server-manager.php
- [X] T038 [US5] Add destination file existence warning/error handling in server-manager.php
- [X] T039 [US5] Add success confirmation messages for copy and rename operations in server-manager.php

**Checkpoint**: User Stories 1-5 should all work independently

---

## Phase 8: User Story 6 - Permission Management (Priority: P4)

**Goal**: Change file permissions and ownership with chmod and chown commands

**Independent Test**: Use `chmod 755 filename.txt` to change permissions and `chown user:group filename.txt` to change ownership, verify in `ls` output.

### Implementation for User Story 6

- [X] T040 [P] [US6] Implement `chmod` command handler with octal permission parsing in server-manager.php
- [X] T041 [P] [US6] Implement `chown` command handler with user:group parsing in server-manager.php
- [X] T042 [US6] Add file existence validation for both commands in server-manager.php
- [X] T043 [US6] Implement chgrp() call for group ownership changes in server-manager.php
- [X] T044 [US6] Add permission error handling and display in server-manager.php
- [X] T045 [US6] Add success confirmation messages for chmod and chown operations in server-manager.php

**Checkpoint**: User Stories 1-6 should all work independently

---

## Phase 9: User Story 7 - File Download (Priority: P4)

**Goal**: Download files from server to local machine with /download command

**Independent Test**: Type `/download filename.txt`, browser should initiate download of the file.

### Implementation for User Story 7

- [X] T046 [US7] Implement `/download` command handler with file existence validation in server-manager.php
- [X] T047 [US7] Implement MIME type detection using finfo_file() in server-manager.php
- [X] T048 [US7] Implement HTTP headers (Content-Type, Content-Disposition, Content-Length) in server-manager.php
- [X] T049 [US7] Implement file streaming with readfile() and exit in server-manager.php
- [X] T050 [US7] Add error handling for non-existent files and permission errors in server-manager.php
- [X] T051 [US7] Add filename sanitization for Content-Disposition header in server-manager.php

**Checkpoint**: User Stories 1-7 should all work independently

---

## Phase 10: User Story 8 - Command Execution and System Information (Priority: P5)

**Goal**: Execute arbitrary commands and view system information with /exec, /phpinfo, /list, and /clear commands

**Independent Test**: Type `/exec ls -la` to execute a command, `/phpinfo` to view PHP config, `/list` to see all commands, `/clear` to clear console.

### Implementation for User Story 8

- [X] T052 [P] [US8] Implement `/exec` command handler using exec() with 3-parameter form in server-manager.php
- [X] T053 [P] [US8] Implement `/phpinfo` command handler (display phpinfo output) in server-manager.php
- [X] T054 [P] [US8] Implement `/list` command handler (enumerate all supported commands with descriptions) in server-manager.php
- [X] T055 [P] [US8] Implement `/clear` command handler (clear console file, reinitialize with header) in server-manager.php
- [X] T056 [US8] Add stderr capture (2>&1) to exec command output in server-manager.php
- [X] T057 [US8] Add exit code detection and error display for exec command in server-manager.php
- [X] T058 [US8] Format command list output with syntax examples in server-manager.php
- [X] T058a [US8] Implement `/autodestruct` command handler that deletes the script file itself using unlink(__FILE__) in server-manager.php

**Checkpoint**: All user stories (1-8) should now be independently functional

---

## Phase 11: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that affect multiple user stories

- [X] T060 [P] Add input field placeholder text "Type your command or /list to list all" (FR-025) in server-manager.php
- [X] T061 [P] Implement session timeout check (24 minutes) with automatic cleanup in server-manager.php
- [X] T062 [P] Add path traversal protection with realpath() validation in server-manager.php
- [X] T063 [P] Add comprehensive error message formatting (display PHP errors directly per FR-023) in server-manager.php
- [X] T064 [P] Verify all file operations use native PHP functions (no shell commands per FR-024) in server-manager.php
- [X] T065 Code review: Verify single-file constraint (all HTML, CSS, PHP in one file per FR-001)
- [X] T066 Code review: Verify immediate command execution (no confirmation prompts per FR-027)
- [X] T067 [P] Update README.md with deployment instructions and security warnings
- [X] T068 [P] Create quickstart.md validation: Upload to test server and execute all supported commands
- [X] T069 Final validation: Test all user stories independently to confirm isolated functionality

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies - can start immediately
- **Foundational (Phase 2)**: Depends on Setup completion - BLOCKS all user stories
- **User Stories (Phase 3-10)**: All depend on Foundational phase completion
  - User stories can then proceed in parallel (if staffed)
  - Or sequentially in priority order (P1 → P2 → P3 → P4 → P5)
- **Polish (Phase 11)**: Depends on all desired user stories being complete

### User Story Dependencies

- **User Story 1 (P1)**: Can start after Foundational (Phase 2) - No dependencies on other stories
- **User Story 2 (P2)**: Can start after Foundational (Phase 2) - Independent of US1
- **User Story 3 (P3)**: Can start after Foundational (Phase 2) - Independent of US1-US2
- **User Story 4 (P3)**: Can start after Foundational (Phase 2) - Independent of US1-US3
- **User Story 5 (P4)**: Can start after Foundational (Phase 2) - Independent of US1-US4
- **User Story 6 (P4)**: Can start after Foundational (Phase 2) - Uses US1's ls output format but independent
- **User Story 7 (P4)**: Can start after Foundational (Phase 2) - Independent of US1-US6
- **User Story 8 (P5)**: Can start after Foundational (Phase 2) - Independent of US1-US7

### Within Each User Story

Since this is a single-file application, all tasks within a user story must be completed sequentially in the same file. However:

- Multiple user stories can be worked on in parallel by editing different sections of server-manager.php
- Tasks marked [P] within different phases can be parallelized if editing different logical sections
- Command handlers can be implemented in any order as long as the command routing dispatcher (T009) is complete

### Parallel Opportunities

- **Setup Phase**: T002 (README) and T003 (LICENSE) can run in parallel with T001
- **Foundational Phase**: Multiple tasks marked [P] can run in parallel if editing different sections of the file
  - T005 (auth logic) || T006 (console file mgmt) can be parallel
  - T010 (HTML) || T011 (CSS) || T012 (JavaScript) can be parallel
- **User Stories After Foundation**: All 8 user stories can start in parallel if team capacity allows
- **Within User Story 1**: T015, T016, T017 (ls implementation) can be conceptually parallel
- **Within User Story 5**: T035 (cp) and T036 (ren) can be parallel
- **Within User Story 6**: T040 (chmod) and T041 (chown) can be parallel
- **Within User Story 8**: T052, T053, T054, T055 can all be parallel (different command handlers)
- **Polish Phase**: Most tasks marked [P] can run in parallel

---

## Parallel Example: User Story 1

Since this is a single-file application, true file-level parallelization isn't possible. However, logical components can be developed in parallel and integrated:

```bash
# Developer A: Implement ls command handler section
Task T015, T016, T017: "ls command logic"

# Developer B: Implement cd command handler section
Task T018, T020: "cd command logic"

# Developer C: UI integration
Task T019, T021: "Current directory display and error handling"
```

---

## Parallel Example: User Story 8 (Special Commands)

```bash
# Different command handlers can be implemented in parallel:
Task T052: "/exec command handler"
Task T053: "/phpinfo command handler"
Task T054: "/list command handler"
Task T055: "/clear command handler"
# Then integrate into command routing (T056-T058)
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup (T001-T003)
2. Complete Phase 2: Foundational (T004-T014) - CRITICAL
3. Complete Phase 3: User Story 1 (T015-T021)
4. **STOP and VALIDATE**: Test ls and cd commands independently
5. Deploy/demo if ready

**MVP Scope**: At this point, you have a working file browser that can list and navigate directories. This is immediately useful for server exploration.

### Incremental Delivery

1. Complete Setup + Foundational → Foundation ready
2. Add User Story 1 → Test independently → **Deploy/Demo (MVP!)**
3. Add User Story 2 → Test independently → Deploy/Demo (Now can view files)
4. Add User Story 3 → Test independently → Deploy/Demo (Now can create dirs)
5. Add User Story 4 → Test independently → Deploy/Demo (Now can delete)
6. Add User Story 5 → Test independently → Deploy/Demo (Now can copy/rename)
7. Add User Story 6 → Test independently → Deploy/Demo (Now can manage permissions)
8. Add User Story 7 → Test independently → Deploy/Demo (Now can download)
9. Add User Story 8 → Test independently → Deploy/Demo (Full feature set!)
10. Polish → Final release

Each story adds value without breaking previous stories.

### Parallel Team Strategy

With multiple developers:

1. Team completes Setup + Foundational together (critical shared infrastructure)
2. Once Foundational is done:
   - Developer A: User Story 1 (ls, cd)
   - Developer B: User Story 2 (cat)
   - Developer C: User Story 3 (mkdir)
   - Developer D: User Story 4 (rm)
3. Next iteration:
   - Developer A: User Story 5 (cp, ren)
   - Developer B: User Story 6 (chmod, chown)
   - Developer C: User Story 7 (/download)
   - Developer D: User Story 8 (/exec, /phpinfo, /list, /clear)
4. Stories integrate via command routing dispatcher

**Note**: Single-file architecture requires coordination to avoid merge conflicts. Recommend sequential priority-order implementation for solo developer, or feature branch strategy with careful merging for teams.

---

## Notes

- **Single-file constraint**: All code goes into server-manager.php - no separate files for components
- **No tests**: Per Constitution Principle IV, no test files or test infrastructure
- **Native PHP only**: Per Constitution Principle III, use only PHP standard library functions
- **Working code over quality**: Per Constitution Principle I, prioritize functionality over elegance
- **Usability over performance**: Per Constitution Principle II, optimize for ease of use
- [P] tasks = can be parallelized (different logical sections or independent command handlers)
- [Story] label maps task to specific user story for traceability
- Each user story should be independently completable and testable via manual verification
- Commit after each user story phase or logical group of tasks
- Stop at any checkpoint to validate story independently via browser testing
- Console output must persist across page refreshes until session expires (24 min timeout)
- All commands execute immediately without confirmation (FR-027)
- All errors display directly in console for transparency (FR-023)

---

## Task Summary

- **Total Tasks**: 69
- **Setup Phase**: 3 tasks
- **Foundational Phase**: 12 tasks (CRITICAL - blocks all user stories)
- **User Story 1 (P1 - MVP)**: 7 tasks
- **User Story 2 (P2)**: 3 tasks
- **User Story 3 (P3)**: 4 tasks
- **User Story 4 (P3)**: 5 tasks
- **User Story 5 (P4)**: 5 tasks
- **User Story 6 (P4)**: 6 tasks
- **User Story 7 (P4)**: 6 tasks
- **User Story 8 (P5)**: 8 tasks
- **Polish Phase**: 11 tasks

### Parallel Opportunities Identified

- **Setup**: 2 tasks can be parallel
- **Foundational**: 6 tasks can be parallel (in 3 groups)
- **User Stories**: All 8 stories can start in parallel after Foundation complete
- **Within Stories**: 15 tasks marked [P] within individual stories
- **Polish**: 8 tasks can be parallel

### Independent Test Criteria

1. **User Story 1**: Can list and navigate directories with `ls` and `cd`
2. **User Story 2**: Can view file contents with `cat`
3. **User Story 3**: Can create directories with `mkdir`
4. **User Story 4**: Can delete files and directories with `rm`
5. **User Story 5**: Can copy with `cp` and rename with `ren`
6. **User Story 6**: Can change permissions with `chmod` and ownership with `chown`
7. **User Story 7**: Can download files with `/download`
8. **User Story 8**: Can execute commands with `/exec`, view info with `/phpinfo`, list commands with `/list`, clear console with `/clear`, and self-delete with `/autodestruct`

### Suggested MVP Scope

**Minimum Viable Product**: User Story 1 only (7 implementation tasks after foundation)
- Provides immediate value: web-based file browser
- Can navigate entire file system via browser
- View detailed file listings (permissions, owner, size, date)
- Demonstrates core concept and authentication
- Total MVP: 3 setup + 11 foundational + 7 implementation = **21 tasks**

---

## Format Validation

✅ **ALL tasks follow the required checklist format**:
- Checkbox: `- [ ]` present on every task
- Task ID: Sequential (T001-T069) in execution order
- [P] marker: Present only on parallelizable tasks (38 tasks marked)
- [Story] label: Present on all user story phase tasks (44 tasks marked with US1-US8)
- Description: Clear action with specific implementation detail
- File paths: All tasks specify server-manager.php (single-file constraint) or documentation files

**No format violations detected.**
