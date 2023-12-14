<?php
declare(strict_types=1);

namespace Tenanted\Core\Support;

use Closure;
use Illuminate\Routing\Router;
use Illuminate\Routing\RouteRegistrar;
use Tenanted\Core\Contracts\IdentityResolver;
use Tenanted\Core\Http\Middleware\TenantedRoute;

/**
 * Base Identity Resolver
 *
 * A base abstract implementation of the {@see \Tenanted\Core\Contracts\IdentityResolver}
 * contract, providing the generic base functionality.
 */
abstract class BaseIdentityResolver implements IdentityResolver
{
    /**
     * @var string
     */
    private string $name;

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @param \Illuminate\Routing\Router $router
     * @param string                     $tenancy
     * @param \Closure|array|string|null $routes
     *
     * @return \Illuminate\Routing\RouteRegistrar
     */
    public function routes(Router $router, string $tenancy, Closure|array|string|null $routes = null): RouteRegistrar
    {
        $route = $router->middleware(TenantedRoute::ALIAS . ':' . $tenancy . ',' . $this->name());

        if ($routes) {
            return $route->group($routes);
        }

        return $route;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }
}