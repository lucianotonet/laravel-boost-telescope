<?php

namespace LucianoTonet\LaravelBoostTelescope;

use Illuminate\Support\ServiceProvider;
use Laravel\Telescope\Contracts\EntriesRepository;
use Laravel\Telescope\Telescope;
use LucianoTonet\LaravelBoostTelescope\MCP\BoostTelescopeServer;
use LucianoTonet\LaravelBoostTelescope\Support\Logger;

/**
 * Service Provider for Laravel Boost Telescope.
 *
 * Registers config, server, commands and the Boost skill provider.
 * Requires Laravel Telescope and Laravel Boost to function.
 */
class BoostTelescopeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->ensureTelescopeIsInstalled();

        if ($this->app->runningInConsole()) {
            $this->registerPublishing();
            $this->registerCommands();
        }

        $this->configureLogging();
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/laravel-boost-telescope.php',
            'laravel-boost-telescope'
        );

        $this->app->singleton(BoostTelescopeServer::class, function ($app) {
            return new BoostTelescopeServer($app->make(EntriesRepository::class));
        });

        $this->app->register(BoostTelescopeSkillServiceProvider::class);
    }

    /**
     * Ensure Laravel Telescope is installed.
     *
     * @throws \RuntimeException
     */
    protected function ensureTelescopeIsInstalled(): void
    {
        if (!class_exists(Telescope::class)) {
            throw new \RuntimeException(
                'Laravel Telescope is required for Laravel Boost Telescope to work. '
                .'Please install it with: composer require laravel/telescope --dev'
            );
        }
    }

    /**
     * Register the package's publishable resources.
     */
    protected function registerPublishing(): void
    {
        $this->publishes([
            __DIR__.'/../config/laravel-boost-telescope.php' => config_path('laravel-boost-telescope.php'),
        ], 'laravel-boost-telescope-config');
    }

    /**
     * Register the package's Artisan commands.
     */
    protected function registerCommands(): void
    {
        $this->commands([
            Console\InstallCommand::class,
            Console\GenerateBoostToolsCommand::class,
        ]);
    }

    /**
     * Configure the logging system.
     */
    protected function configureLogging(): void
    {
        Logger::getInstance();
    }
}
