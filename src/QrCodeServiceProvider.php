<?php

namespace LaraBoy\LaraQrcode;

use Illuminate\Support\ServiceProvider;

class QrCodeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/../config/qrcode.php' => $this->app->basePath('config/qrcode.php'),
        ], 'config');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\GenerateQrCodeCommand::class,
            ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Merge default configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../config/qrcode.php',
            'qrcode'
        );

        // Bind the QR Code generator service
        $this->app->singleton('qrcode', function () {
            return new Services\QrCodeGenerator();
        });
    }
}
