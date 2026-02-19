<!--
Sync Impact Report
==================
Version Change: 0.0.0 → 1.0.0
Modified Principles: N/A (initial constitution)
Added Sections: All core principles, Development Constraints, Governance
Removed Sections: N/A
Templates Requiring Updates:
  - ✅ .specify/templates/plan-template.md (Constitution Check section validated)
  - ✅ .specify/templates/spec-template.md (No testing requirements removed from mandatory sections)
  - ✅ .specify/templates/tasks-template.md (Testing tasks marked as optional - already compliant)
Follow-up TODOs: None
-->

# PHP Server Manager Constitution

## Core Principles

### I. Working Code Over Quality

**MUST prioritize functional, working code over perfect code quality.**

- Pragmatism over perfection: Ship working solutions first, refine later if needed
- Focus on functionality: Code that works is better than code that's elegant but incomplete
- Avoid premature optimization: Don't spend excessive time on code quality improvements that don't directly contribute to functionality
- Technical debt is acceptable: Accumulating some technical debt is acceptable if it accelerates delivery of working features

**Rationale**: In many contexts, especially prototyping, proof-of-concepts, and rapid development scenarios, the value of having working functionality outweighs the long-term benefits of pristine code. This principle acknowledges that iteration on working code is often more productive than pursuing perfection before shipping.

### II. Usability Over Performance

**MUST focus on usability and developer experience rather than raw performance optimization.**

- Developer experience first: APIs, interfaces, and code structure should prioritize ease of use and comprehension
- User-friendly over fast: If a feature is easy to use but slower, prefer usability unless performance critically impacts the user experience
- Avoid premature optimization: Do not optimize for performance without measured evidence that optimization is necessary
- Clarity over cleverness: Code should be obvious and self-documenting rather than highly optimized but obscure

**Rationale**: Most applications spend the majority of their execution time in a small fraction of code. Optimizing prematurely wastes effort and often introduces complexity that harms usability. Prioritizing usability ensures the codebase remains maintainable and accessible, which has compounding long-term benefits.

### III. Native Methods Only

**MUST use only native/built-in methods and language features. External dependencies are PROHIBITED.**

- Zero external packages: No external packages, modules, libraries, or third-party dependencies are allowed
- Standard library only: Use only the language's standard library and built-in functionality
- No package managers: Do not use composer, npm, pip, or any package management tools for dependencies
- Self-contained implementation: All functionality must be implemented using native language features

**Rationale**: This constraint ensures the project remains lightweight, has zero supply-chain security risks, requires no dependency management, and is maximally portable. It also enforces deep understanding of the language's capabilities rather than relying on external abstractions.

### IV. No Testing Required

**Testing is NOT required and MUST NOT be added to the project.**

- No test files: Do not create test files, test suites, or test directories
- No test frameworks: Do not add testing frameworks, assertion libraries, or test runners
- No test infrastructure: Do not add CI/CD testing pipelines, test configuration, or test documentation
- Manual verification only: Features should be manually verified through usage and inspection

**Rationale**: For certain project types—rapid prototypes, personal tools, experimental code, learning projects—the overhead of maintaining tests outweighs their benefits. This principle explicitly removes testing obligations to maximize development velocity and reduce boilerplate infrastructure.

## Development Constraints

### Dependency Policy

**Enforced by Principle III (Native Methods Only)**:

- All functionality MUST be implemented using the language's standard library
- External packages, modules, or libraries are strictly PROHIBITED
- Package manager configuration files (composer.json, package.json, etc.) MUST NOT be present unless they contain zero dependencies
- If a feature cannot be implemented with native methods alone, it MUST be reconsidered or descoped

### Code Review Focus

**Based on Core Principles**:

During code reviews, verify:

1. **Functionality**: Does the code work as intended? (Principle I)
2. **Usability**: Is the code easy to understand and use? (Principle II)
3. **Native implementation**: Are only native methods used? (Principle III)
4. **No tests present**: Confirm no test code has been added (Principle IV)

Code reviews MUST NOT focus on:

- Code style or formatting nitpicks (unless it significantly harms readability)
- Performance optimizations (unless performance critically impacts usability)
- Architectural purity or design pattern adherence
- Test coverage or testing strategies

## Governance

### Amendment Process

This constitution governs all development practices for the PHP Server Manager project. To amend:

1. Propose change with rationale in project discussion or issue
2. Document impact on existing code and workflows
3. Require approval from project maintainer(s)
4. Update this document with new version number following semantic versioning
5. Update all dependent templates and documentation

### Versioning Policy

Constitution versions follow semantic versioning (MAJOR.MINOR.PATCH):

- **MAJOR**: Backward-incompatible governance changes (e.g., removing a core principle, reversing a mandatory rule)
- **MINOR**: Additive changes (e.g., new principle added, new constraint section, material guidance expansion)
- **PATCH**: Clarifications, wording improvements, typo fixes, non-semantic refinements

### Compliance Review

All pull requests, feature implementations, and design decisions MUST be verified against this constitution:

- Core Principles are NON-NEGOTIABLE and MUST be followed
- Violations must be explicitly justified with reference to specific circumstances
- Complexity or deviations require documentation in plan.md Complexity Tracking section
- When in doubt, principles override convenience

For day-to-day development guidance and command usage, refer to `.specify/templates/commands/` directory and project documentation.

**Version**: 1.0.0 | **Ratified**: 2026-02-19 | **Last Amended**: 2026-02-19
