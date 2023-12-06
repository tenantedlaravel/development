<?php
declare(strict_types=1);

namespace Tenanted\Core\Resolvers;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Routing\RouteRegistrar;
use Tenanted\Core\Contracts\Tenancy;
use Tenanted\Core\Support\ParameterIdentityResolver;

/**
 * Path Identity Resolver
 *
 * This implementation of {@see \Tenanted\Core\Contracts\IdentityResolver}, uses
 * a URI path segment as a tenant identifier.
 */
class PathIdentityResolver extends ParameterIdentityResolver
{
    /**
     * The path segment of the URI containing the identifier
     *
     * @var int<0, max>
     */
    private int $segment;

    /**
     * @param string $name
     * @param int    $segment
     */
    public function __construct(string $name, int $segment = 0)
    {
        parent::__construct($name);

        $this->segment = $segment;
    }

    /**
     * @param \Illuminate\Http\Request         $request
     * @param \Tenanted\Core\Contracts\Tenancy $tenancy
     *
     * @return string|false|null
     */
    public function resolve(Request $request, Tenancy $tenancy): string|null|false
    {
        if ($request->route() !== null) {
            return parent::resolve($request, $tenancy);
        }

        return $request->segment($this->segment);
    }

    /**
     * @param \Illuminate\Routing\Router $router
     * @param string                     $tenancy
     * @param array|\Closure|string|null $routes
     *
     * @return \Illuminate\Routing\RouteRegistrar
     */
    public function routes(Router $router, string $tenancy, array|Closure|string|null $routes = null): RouteRegistrar
    {
        return parent::routes($router, $tenancy, $routes)
                     ->prefix('{' . $this->getParameterName($tenancy) . '}');
    }
}