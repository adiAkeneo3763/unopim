# Changelog

All notable changes to UnoPim will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [v2.0.0] - 2026-03-23

### Added

#### AI Agent Chat
- Introduced AI Agent chat interface for conversational product management.
- Added 27+ PIM tool actions accessible via natural language (search, create, update, delete, bulk edit, export, categorize, generate content/images, etc.).
- Added `AgentRunner` orchestrator with Prism-based agentic loop and multi-step tool calling.
- Added `ToolRegistry` for dynamic tool discovery and registration.
- Added `ChatContext` DTO for managing conversation state across tool calls.
- Added `EmbeddingSimilarityService` and `SemanticRankingService` for AI-powered product search and ranking.
- Added `agent_conversations` and `agent_conversation_messages` database tables.

#### MagicAI Platform Management
- Added multi-platform AI provider support with database-backed credential management.
- Added `MagicAIPlatform` model with encrypted API key storage and active/default scoping.
- Added `MagicAIPlatformController` with full CRUD, connection testing, and dynamic model fetching.
- Added `MagicAIPlatformDataGrid` for admin interface listing.
- Added `AiProvider` enum supporting OpenAI, Anthropic, Gemini, Groq, Ollama, XAI, Mistral, DeepSeek, Azure, and OpenRouter.
- Added `LaravelAiAdapter` as unified bridge between MagicAI and Laravel AI SDK / Prism.
- Added `MagicContentAgent` implementing Laravel AI's Agent interface.
- Added admin UI for configuring text generation, image generation, and translation platforms/models.

#### Dashboard Enhancements
- Added channel readiness dashboard widget.
- Added product trend visualization widget.
- Added recent activity feed widget.
- Added needs-attention summary widget.
- Added product stats overview widget.
- Added data transfer status widget.
- Added `RefreshDashboardCacheCommand` artisan command with scheduled execution every 10 minutes.

#### CI/CD & Testing
- Added `translation_tests.yml` workflow for automated translation file auditing.
- Added `TranslationsChecker` artisan command for detecting missing/extra translation keys.
- Added Composer dependency caching to all CI workflows.
- Added concurrency groups to prevent duplicate workflow runs.

#### Configuration
- Added `config/ai.php` for Laravel AI SDK provider configuration.
- Added `bootstrap/providers.php` for centralized service provider registration (Laravel 12 pattern).

### Changed

#### Framework Upgrade - Laravel 12
- Upgraded `laravel/framework` from `^10.0` to `^12.0`.
- Upgraded minimum PHP requirement from `8.2` to `8.3`.
- Migrated application bootstrap to Laravel 12's `Application::configure()` fluent API in `bootstrap/app.php`.
- Moved all service provider registration to `bootstrap/providers.php`.
- Moved middleware configuration from `app/Http/Kernel.php` to `bootstrap/app.php` using `withMiddleware()`.
- Moved console scheduling from `app/Console/Kernel.php` to `bootstrap/app.php` using `withSchedule()`.
- Moved exception handling from `app/Exceptions/Handler.php` to `bootstrap/app.php` using `withExceptions()`.
- Updated `app/Http/Controllers/Controller.php` to simplified base controller.

#### Dependency Updates
- Upgraded `laravel/sanctum` from `^3.2` to `^4.0`.
- Upgraded `diglactic/laravel-breadcrumbs` from `^8.0` to `^10.0`.
- Upgraded `astrotomic/laravel-translatable` from `^11.0.0` to `^11.16.0`.
- Upgraded `pestphp/pest` to `^3.0` and `phpunit/phpunit` to `^11.0`.
- Upgraded `nunomaduro/collision` to `^8.0`.
- Added `laravel/ai` `^0.3.2` and `laravel/boost` `^2.1` as new dependencies.
- Updated `barryvdh/laravel-dompdf` to accept `^2.0.0|^3.0`.
- Updated `intervention/image` to accept `^2.4|^3.0`.

#### Image Cache System
- Replaced `ImageManager` with new `ImageCache` system featuring deferred execution and closure hashing.
- Added `CachedImage` wrapper for transparent image interface proxying.
- Added `HashableClosure` for reliable closure-based filter caching.
- Added `Small`, `Medium`, and `Large` template classes for standardized image transformations.
- Updated `Controller` with ETag support and conditional response handling.

#### MagicAI Refactor
- Replaced individual provider service classes (`OpenAI`, `Gemini`, `Groq`, `Ollama`) with unified `LaravelAiAdapter`.
- Migrated AI credentials from `core_config` table to dedicated `magic_ai_platforms` table.
- Extended `magic_ai_prompts` type enum to include `image` and `translation` types.
- Added `purpose` column to `magic_ai_prompts` for separating text generation, image generation, and translation prompts.

#### CI Workflows
- Updated all workflows to use PHP 8.3.
- Updated Node.js from v18 to v20 and actions/checkout to v4 in Playwright workflow.
- Added explicit permissions configuration to all workflows.

#### Localization
- Updated translation files across all 30+ supported locales with new keys for AI agent, MagicAI platforms, dashboard widgets, and completeness features.

### Removed

- Removed `app/Console/Kernel.php` (replaced by `bootstrap/app.php` scheduling).
- Removed `app/Http/Kernel.php` (replaced by `bootstrap/app.php` middleware).
- Removed `app/Exceptions/Handler.php` (replaced by `bootstrap/app.php` exceptions).
- Removed `app/Http/Middleware/EncryptCookies.php` (handled by `bootstrap/app.php`).
- Removed `app/Http/Middleware/RedirectIfAuthenticated.php` (handled by framework).
- Removed `app/Http/Middleware/TrimStrings.php` (handled by `bootstrap/app.php`).
- Removed `app/Http/Middleware/TrustHosts.php` (handled by framework).
- Removed `app/Http/Middleware/TrustProxies.php` (handled by `bootstrap/app.php`).
- Removed `app/Http/Middleware/VerifyCsrfToken.php` (handled by framework).
- Removed `app/Providers/AuthServiceProvider.php` (consolidated into `AppServiceProvider`).
- Removed `app/Providers/BroadcastServiceProvider.php` (consolidated into `AppServiceProvider`).
- Removed `app/Providers/EventServiceProvider.php` (consolidated into `AppServiceProvider`).
- Removed `app/Providers/RouteServiceProvider.php` (replaced by `bootstrap/app.php` routing).
- Removed `packages/Webkul/MagicAI/src/Services/OpenAI.php` (replaced by `LaravelAiAdapter`).
- Removed `packages/Webkul/MagicAI/src/Services/Gemini.php` (replaced by `LaravelAiAdapter`).
- Removed `packages/Webkul/MagicAI/src/Services/Groq.php` (replaced by `LaravelAiAdapter`).
- Removed `packages/Webkul/MagicAI/src/Services/Ollama.php` (replaced by `LaravelAiAdapter`).
- Removed `packages/Webkul/Core/src/ImageCache/ImageManager.php` (replaced by `ImageCache`).

---

## [v1.0.0] - 2024-12-16

### Added
- Product completeness scoring with configurable required attributes per channel/locale.
- System queue (`--queue=system,default`) for internal job processing.
- Attribute option datagrid for managing large select/multiselect datasets.
- Dynamic column and filter management in product datagrid.
- Export performance optimization with ElasticSearch-based cursor pagination.
- Streaming file generation via OpenSpout for CSV/XLSX exports.
- JSON file buffering for intermediate export data.

### Changed
- Export source refactored to handle ElasticSearch-based and database exports.
- Export finalization workflow changed to use `flush()` in `Export.php`.
- `AbstractExporter` constructor updated with `$exportBuffer` dependency.
- `BufferInterface::addData` signature simplified.
- Code validation rule updated to allow only underscore as special character.

---

## [v0.3.0] - 2024-10-01

- See [UPGRADE-0.2.x-0.3.0.md](UPGRADE-0.2.x-0.3.0.md) for details.

## [v0.2.0] - 2024-08-01

- See [UPGRADE-0.1.x-0.2.0.md](UPGRADE-0.1.x-0.2.0.md) for details.
