# Changelog

All notable changes to `laravel-boost-telescope` will be documented in this file.

## [Unreleased]

## [1.0.0] - 2026-02-02

### Added
- Initial release as Laravel Boost plugin for Telescope integration
- 19 specialized debugging tools accessible via Laravel Boost MCP server
- `BoostTelescopeServiceProvider` for automatic tool discovery and registration
- `BoostTelescopeSkillServiceProvider` for Boost skills integration
- `TelescopeBoostTool` base class for tool wrappers
- Skills and Guidelines for AI-assisted debugging

### Tools
- `telescope_exceptions` - Exception tracking with stack traces
- `telescope_queries` - Database query analysis with slow query detection
- `telescope_requests` - HTTP request/response logging with related entries
- `telescope_logs` - Application log entries
- `telescope_jobs` - Queue job execution monitoring
- `telescope_batches` - Batch job processing
- `telescope_cache` - Cache operations analysis
- `telescope_redis` - Redis operations monitoring
- `telescope_models` - Eloquent model operations
- `telescope_mail` - Email tracking
- `telescope_notifications` - Notification dispatching
- `telescope_commands` - Artisan command execution
- `telescope_schedule` - Scheduled task monitoring
- `telescope_events` - Event dispatching
- `telescope_gates` - Authorization gate checks
- `telescope_views` - View rendering
- `telescope_dumps` - Debug dumps (dump/dd)
- `telescope_http_client` - Outgoing HTTP requests
- `telescope_prune` - Telescope entry cleanup

### Requirements
- PHP 8.2+
- Laravel 11.x or 12.x
- Laravel Telescope 5.0+
- Laravel Boost 2.0+