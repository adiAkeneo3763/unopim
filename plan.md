# Laravel 10 to 12 Upgrade Plan for UnoPim

## Document Control
- Project: UnoPim Laravel Core Upgrade
- Current Target: Laravel 10.x -> Laravel 12.x
- Owner: <assign-owner>
- Technical Lead: <assign-tech-lead>
- QA Lead: <assign-qa-lead>
- Start Date: <yyyy-mm-dd>
- Planned Release Date: <yyyy-mm-dd>
- Status: Draft

## 1. Objective
Upgrade UnoPim from Laravel 10 to Laravel 12 in a controlled, test-driven, and rollback-safe manner while preserving existing business behavior, package compatibility, and module conventions.

## 2. Success Criteria
- Application boots and serves successfully on Laravel 12.
- All critical UnoPim workflows continue to work (admin, products, imports/exports, queue jobs, ACL routes).
- All Laravel 11 and 12 upgrade-guide impact items are reviewed and marked: Not Applicable / Addressed / Verified.
- No unresolved production-blocking regressions.
- All required quality gates pass:
  - `./vendor/bin/pint`
  - `./vendor/bin/pest` (full suite and critical targeted suites)
  - `php artisan optimize:clear`
- Upgrade notes and rollback plan are complete.

## 3. Scope
### In Scope
- Framework upgrade path from Laravel 10 to Laravel 12 (including intermediate compatibility checks).
- Composer dependency alignment and transitive package compatibility.
- Config/bootstrap/runtime compatibility updates.
- UnoPim package layer verification under `packages/Webkul/`.
- Test stabilization and required code changes for Laravel 12 compatibility.

### Out of Scope
- New feature development unrelated to upgrade.
- Refactoring for style only.
- Non-essential architectural rewrites.

## 4. Core Constraints (UnoPim-Specific)
- UnoPim conventions are source of truth.
- Keep changes minimal and traceable.
- Preserve module/service boundaries and repository patterns.
- No broad refactors without explicit approval.

## 5. Branching and Release Strategy
- Create dedicated branch: `upgrade/laravel-12`.
- Use small, reviewable commits by phase.
- Tag backup baseline before upgrade:
  - Git tag: `pre-laravel12-upgrade`
- Versioning target for this initiative:
  - Application/package release: `1.0.0 -> 2.0.0`
- Release via staged environments:
  - Local -> CI -> Staging -> Production

## 6. Phase Plan

### Phase 0: Preparation and Baseline
- Freeze non-upgrade feature merges during critical upgrade window.
- Capture baseline metrics:
  - Current test pass/fail state
  - Known warnings/errors
  - Key endpoint smoke checks
- Export dependency inventory:
  - `composer show`
  - `composer outdated`

Deliverables:
- Baseline report
- Dependency risk matrix

### Phase 1: Compatibility Assessment
- Review Laravel 11 and 12 upgrade guides and breaking changes.
- Map impacted areas:
  - Middleware and bootstrap flow
  - Exception handling and logging
  - Queue/events/scheduling
  - Auth, validation, routing, config behavior
- Validate all critical package compatibility (especially ecosystem and custom packages).

Deliverables:
- Compatibility checklist with package status: Compatible / Needs Update / Replace / Blocked
- Upgrade impact matrix for Laravel 11 and Laravel 12 guide items

#### Laravel 11 Impact Checklist (10 -> 11)
- Platform requirements:
  - PHP >= 8.2
  - curl >= 7.34
  - MySQL and PostgreSQL runtime/version compatibility validated in target environments
- Composer upgrades (as applicable):
  - `laravel/framework:^11.0`
  - `nunomaduro/collision:^8.1`
  - Ecosystem packages (if installed): Sanctum 4, Passport 12, Telescope 5, Cashier 15, Spark 5, etc.
- Migration publishing checks (if these packages exist):
  - `cashier-migrations`, `passport-migrations`, `sanctum-migrations`, `spark-migrations`, `telescope-migrations`
- Application structure decision:
  - Keep current Laravel 10-style app structure during upgrade (recommended for existing apps)
  - Do not force immediate migration to new Laravel 11 skeleton
- Database and schema checks:
  - Column modification behavior (`change()` must re-specify retained modifiers)
  - Floating point migration updates (`float`/`double` behavior)
  - Doctrine DBAL removal impact
  - Deprecated schema inspection API changes
  - MySQL and PostgreSQL schema behavior compatibility review
- Auth and contract checks:
  - Password rehashing behavior
  - `UserProvider` new method (`rehashPasswordIfRequired`) if custom provider exists
  - `Authenticatable` new method (`getAuthPasswordName`) if custom implementation exists
- Rate limiting and queue checks:
  - Per-second decay behavior review
  - Sync queue `after_commit` behavior changes
- Date/time checks:
  - Carbon 3 behavior compatibility (diff methods, sign/float behavior)

#### Laravel 12 Impact Checklist (11 -> 12)
- Composer/test stack upgrades:
  - `laravel/framework:^12.0`
  - `phpunit/phpunit:^11.0`
  - `pestphp/pest:^3.0`
- Carbon requirement:
  - Carbon 3 required (no Carbon 2 fallback)
- Eloquent and UUID behavior:
  - `HasUuids` now UUIDv7-compatible
  - If UUIDv4 behavior required, explicitly use `HasVersion4Uuids`
- Routing and request checks:
  - Route name precedence behavior consistency
  - Nested array merge behavior via `mergeIfMissing` dot notation
- Storage and validation checks:
  - `local` disk default root path (`storage/app/private` unless configured)
  - `image` validation excludes SVG by default; allow explicitly if needed
- Container/DI behavior:
  - Constructor property default value handling changed in container resolution
- Database inspection / internals:
  - Multi-schema inspection output changes
  - Constructor signature changes for low-level DB classes (if custom DB drivers/grammars exist)

### Phase 1A: New Application Structure Plan (UnoPim-Safe)
Goal: Evaluate Laravel 11+ structure changes without destabilizing existing UnoPim architecture.

Track A (recommended for upgrade release):
- Keep existing UnoPim/Laravel 10 style structure during 10 -> 12 upgrade.
- Apply only compatibility changes required by framework and packages.
- Ensure providers, middleware, routes, exceptions keep existing behavior.

Track B (optional post-upgrade modernization):
- Create separate post-upgrade plan to selectively adopt Laravel 11+ skeleton patterns.
- Evaluate migration of provider registration patterns (`bootstrap/providers.php`) only where safe.
- Compare against `laravel/laravel` `10.x...11.x` and `11.x...12.x` skeleton diffs and adopt incrementally.

Deliverables:
- Signed architecture decision record: Track A now, Track B later (or justified exception)
- Compatibility map of current structure vs Laravel 11/12 structure expectations

### Phase 2: Composer Upgrade Execution
- Update composer constraints for Laravel 12-compatible package versions.
- Upgrade in controlled steps (prefer intermediate validation checkpoints).
- Run composer install/update and resolve conflicts intentionally.

Execution order:
1. Upgrade to Laravel 11-compatible dependency set and stabilize.
2. Run full validation and close all high-impact issues.
3. Upgrade from stabilized 11 state to Laravel 12 dependency set.
4. Run full validation again.

Validation commands:
- `composer validate`
- `composer install`
- `composer update` (scoped and controlled)

Deliverables:
- Updated `composer.json` and lock
- Conflict resolution log

### Phase 3: Framework and Runtime Fixes
- Apply framework-level code and config compatibility changes.
- Fix bootstrapping, provider registration, and runtime compatibility issues.
- Validate environment and cache behavior.

Required review points:
- Auth behavior (password rehash, custom contracts)
- Rate limiter behavior changes (seconds-based decay)
- Queue behavior changes (`after_commit` semantics)
- Storage path defaults and explicit local disk definition
- Validation behavior changes (SVG with `image` rule)
- Any custom interfaces/implementations touched by framework method signature changes

Validation commands:
- `php artisan about`
- `php artisan optimize:clear`
- `php artisan route:list`
- `php artisan config:show app`

Deliverables:
- Runtime compatibility patch set

### Phase 4: UnoPim Package and Module Verification
- Verify all `packages/Webkul/*` modules for Laravel 12 compatibility.
- Validate:
  - Service providers
  - Route registration
  - ACL/menu/config loading
  - Repository/model contracts
  - Import/export workflows and queue usage
- Check for broken references and namespace issues.

UnoPim package-specific checklist:
- `packages/Webkul/*/src/Providers/*` boot and register behavior
- Config merge consistency for ACL/menu/importers/exporters
- Middleware references and auth guards
- Passport/Sanctum integration behavior if installed/used
- Import/export queue processing and retries
- Custom DB/schema code for removed/deprecated APIs

Deliverables:
- Module compatibility checklist
- Fixes by package with owners

### Phase 5: Test and Quality Stabilization
- Run formatter and tests repeatedly until stable.
- Prioritize failing tests by risk category:
  - Critical business flow
  - Data integrity
  - Auth and permissions
  - Import/export and queue processing

Required commands:
- `./vendor/bin/pint`
- `./vendor/bin/pest`
- `./vendor/bin/pest --testsuite="Admin Feature Test"`

Additional upgrade validation commands:
- `php artisan about`
- `php artisan route:list`
- `php artisan config:clear && php artisan cache:clear && php artisan optimize:clear`
- `php artisan test` (if configured separately)

Deliverables:
- Stable test report
- Known accepted risks list (if any)

### Phase 6: End-to-End Validation and UAT
- Smoke test critical user journeys:
  - Admin login and permissions
  - Product CRUD and indexing flows
  - Attribute and category management
  - Import/export jobs and queue processing
- Validate logs for hidden runtime warnings.

Validation commands:
- `php artisan queue:work --queue="default,system"`
- Check application logs after smoke runs

Deliverables:
- UAT sign-off report
- Go/No-Go checklist

### Phase 7: Release and Post-Release Monitoring
- Deploy to production with rollback readiness.
- Monitor health during stabilization window.
- Capture post-release issues and close-out report.

Deliverables:
- Release notes
- Post-release monitoring report

### Phase 8: Upgrade Documentation and User-Facing Deliverables
Goal: Ensure users and developers can upgrade safely from `1.0.0` to `2.0.0` with clear instructions and migration tooling.

Required files to create/update:
- `upgrade_1.0.0_to_2.0.0.sh` (user-facing upgrade script)
- `UPGRADE.md` (master upgrade guide)
- `UPGRADE-1.0.0-2.0.0.md` (version-specific upgrade document)
- `Changelog.md` (release notes with breaking changes)

#### 8.1 Testing and Quality Requirements (Mandatory)
Before finalizing upgrade artifacts and release notes, run:
- `./vendor/bin/pint`
- `./vendor/bin/pest`
- `./vendor/bin/pest --testsuite="Admin Feature Test"`
- `php artisan optimize:clear`

Record in release notes:
- Date/time of test run
- Command list executed
- Pass/fail summary

#### 8.2 Upgrade Script Requirements (`upgrade_1.0.0_to_2.0.0.sh`)
Script must be safe, idempotent where possible, and clearly logged.

Minimum script behavior:
1. Preflight checks:
  - PHP version compatibility
  - Composer availability
  - Required extensions and permissions
2. Maintenance flow:
  - Backup reminder/checkpoint
  - Dependency install/update
  - Cache clear / optimize clear
3. Migration and runtime steps:
  - DB migrations (with prompt or safe mode)
  - Asset/build instructions if required
4. Post-upgrade checks:
  - Basic health checks
  - Queue worker restart note (if needed)
5. Rollback instructions on failure

#### 8.3 `UPGRADE.md` Requirements
`UPGRADE.md` must include:
- Supported upgrade paths
- General prerequisites
- Backup and rollback checklist
- Step-by-step upgrade process
- Troubleshooting section
- Link/reference to `UPGRADE-1.0.0-2.0.0.md`

#### 8.4 `UPGRADE-1.0.0-2.0.0.md` Requirements
Version-specific guide must include:
- Exact version jump (`1.0.0 -> 2.0.0`)
- Breaking changes summary
- Configuration changes required
- Environment variable changes (added/removed/renamed)
- Database migration notes
- Manual action items for admins/users

#### 8.5 `Changelog.md` Requirements
For release `2.0.0`, changelog must contain:
- Features added
- Improvements
- Fixes
- Breaking changes (clearly marked)
- Files removed
- Files moved/renamed
- Upgrade impact notes for integrators

Formatting recommendation:
- Use clear headings per version
- Keep entries concise and action-oriented
- For breaking changes, include mitigation/upgrade action

## 7. Risk Register
- Package incompatibility blocks Laravel 12.
- Hidden behavioral change in framework internals.
- Queue/job failures in import-export flows.
- Config/cache differences causing environment-specific bugs.
- Test suite gaps missing production regressions.

Mitigation:
- Stage-gated rollout, aggressive smoke tests, explicit rollback criteria.

## 8. Rollback Plan
Rollback triggers:
- Critical production errors impacting admin/product flows.
- Data integrity risk in write operations.
- Queue failure rate above acceptable threshold.

Rollback steps:
1. Stop deployment pipeline.
2. Revert to `pre-laravel12-upgrade` tag.
3. Restore last stable artifact and lockfile.
4. Run `php artisan optimize:clear` and restart workers.
5. Validate smoke checks and logs.

## 9. Ownership Matrix
- Framework upgrade lead: <name>
- Dependency conflict lead: <name>
- UnoPim package validation lead: <name>
- Test stabilization lead: <name>
- Release manager: <name>

## 10. Task Checklist
- [ ] Baseline captured and approved
- [ ] Compatibility matrix complete
- [ ] Laravel 11 high/medium/low impact checklist completed
- [ ] Laravel 12 high/medium/low impact checklist completed
- [ ] New application structure decision recorded (Track A vs Track B)
- [ ] Composer constraints updated
- [ ] Runtime framework issues resolved
- [ ] Package/module verification complete
- [ ] Pint and Pest passing
- [ ] UAT completed
- [ ] Rollback tested
- [ ] `upgrade_1.0.0_to_2.0.0.sh` created and validated
- [ ] `UPGRADE.md` updated
- [ ] `UPGRADE-1.0.0-2.0.0.md` created/updated
- [ ] `Changelog.md` updated with breaking changes and file move/remove notes
- [ ] Production release approved

## 11. Daily Update Template
### Date
<yyyy-mm-dd>

### Completed Today
- 

### In Progress
- 

### Blockers
- 

### Risks Added/Changed
- 

### Plan for Tomorrow
- 

## 12. Final Sign-Off
- Engineering Lead: <name/date>
- QA Lead: <name/date>
- Product/Stakeholder: <name/date>
