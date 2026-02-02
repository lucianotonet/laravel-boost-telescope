# Laravel Boost Telescope

<a href="https://github.com/lucianotonet/laravel-boost-telescope/actions/workflows/tests.yml"><img src="https://github.com/lucianotonet/laravel-boost-telescope/actions/workflows/tests.yml/badge.svg" alt="Tests"></a>
<a href="https://packagist.org/packages/lucianotonet/laravel-boost-telescope"><img src="https://img.shields.io/packagist/v/lucianotonet/laravel-boost-telescope.svg" alt="Latest Version on Packagist"></a>
<a href="https://packagist.org/packages/lucianotonet/laravel-boost-telescope"><img src="https://img.shields.io/packagist/dt/lucianotonet/laravel-boost-telescope.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/lucianotonet/laravel-boost-telescope"><img src="https://img.shields.io/packagist/l/lucianotonet/laravel-boost-telescope.svg" alt="License"></a>


**Laravel Boost Plugin for Telescope**

*Give your AI assistant the power to debug with Telescope's data.*


## Requirements

- PHP 8.2+
- Laravel 11 or 12
- Laravel Telescope 5.0+
- Laravel Boost 2.0+

## Installation

```bash
composer require lucianotonet/laravel-boost-telescope --dev
```

### Integration with Laravel Boost

After installation, run:

```bash
php artisan boost:install

# or

php artisan boost:update
```

The Telescope debugging tools will be automatically discovered and available to your AI assistant through the Boost MCP server.

## Configuration

### Environment Variables

```env
# Enable/disable the package
LARAVEL_BOOST_TELESCOPE_ENABLED=true

# Logging
LARAVEL_BOOST_TELESCOPE_LOGGING_ENABLED=true
LARAVEL_BOOST_TELESCOPE_LOG_CHANNEL=stack
```

### Configuration File

Publish and customize the configuration:

```bash
php artisan vendor:publish --tag=laravel-boost-telescope-config
```

## Available Tools

The package provides 20 specialized debugging tools:

### Core Debugging
- `telescope_exceptions` - Application exceptions with stack traces
- `telescope_queries` - Database queries with timing and bindings
- `telescope_requests` - HTTP requests with headers and payloads
- `telescope_logs` - Application logs with context

### Queue & Jobs
- `telescope_jobs` - Queue job execution and failures
- `telescope_batches` - Batch job processing

### Cache & Data
- `telescope_cache` - Cache hits, misses, and writes
- `telescope_redis` - Redis operations
- `telescope_models` - Eloquent model operations

### Communication
- `telescope_mail` - Sent emails
- `telescope_notifications` - Dispatched notifications

### System
- `telescope_commands` - Artisan command execution
- `telescope_schedule` - Scheduled task execution
- `telescope_events` - Event dispatching
- `telescope_gates` - Authorization gate checks
- `telescope_views` - View rendering
- `telescope_dumps` - Debug dumps (dump(), dd())
- `telescope_http_client` - Outgoing HTTP requests

### Maintenance
- `telescope_prune` - Clean up old entries

## Usage Examples

### Debugging Slow Queries

Ask your AI assistant:

> "Find slow queries in my application"

The AI will use `telescope_queries` with `slow: true` to identify problematic queries.

### Investigating Exceptions

> "What exceptions occurred in the last hour?"

The AI will use `telescope_exceptions` to retrieve and analyze recent errors.

### Analyzing Request Flow

> "Show me the details of the last failed request"

The AI will use `telescope_requests` filtered by status code to find and analyze the failure.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for recent changes.

## Contributing

Contributions are welcome! Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover a security vulnerability, please email tonetlds@gmail.com instead of using the issue tracker.

## Credits

- [Luciano Tonet](https://github.com/lucianotonet)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
