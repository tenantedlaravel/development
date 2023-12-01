<?php
declare(strict_types=1);

namespace Tenanted\Core;

use Illuminate\Support\ServiceProvider;

class TenantedServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Publish the config
        $this->publishes([__DIR__ . '/../../config/tenanted.php' => config_path('tenanted.php')]);
    }

    public function register(): void
    {
        $this->registerManager();
    }

    /**
     * Register the tenanted manager
     *
     * This method registers the {@see \Tenanted\Core\TenantedManager} class as
     * a singleton with Larvels IOC container.
     *
     * @return void
     */
    private function registerManager(): void
    {
        $this->app->bind(TenantedManager::class, function () {
            return new TenantedManager($this->app);
        }, true);
    }
}