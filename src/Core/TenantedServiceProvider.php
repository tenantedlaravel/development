<?php
declare(strict_types=1);

namespace Tenanted\Core;

use Illuminate\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use Tenanted\Core\Http\Middleware\SetTenantHeader;
use Tenanted\Core\Http\Middleware\TenantedRoute;
use Tenanted\Core\Listeners\RouteMatchedListener;

class TenantedServiceProvider extends ServiceProvider
{
    /**
     * Boots the tenanted package
     *
     * @return void
     */
    public function boot(): void
    {
        // Publish the config
        $this->publishes([__DIR__ . '/../../config/tenanted.php' => config_path('tenanted.php')]);

        // Register the RouteMatched listener
        $this->app->get(Dispatcher::class)->subscribe(RouteMatchedListener::class);
    }

    /**
     * Register the tenanted package
     *
     * @return void
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function register(): void
    {
        $this->registerManager();
        $this->registerMiddleware();
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
        },               true);
    }

    /**
     * Register the package middleware
     *
     * @return void
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function registerMiddleware(): void
    {
        /**
         * @var \Illuminate\Routing\Router $router
         */
        $router = $this->app->get('router');

        // Create an alias for the identification middleware
        $router->aliasMiddleware(TenantedRoute::ALIAS, TenantedRoute::class);

        // Create an alias for the header middleware
        $router->aliasMiddleware(SetTenantHeader::ALIAS, SetTenantHeader::class);
    }
}