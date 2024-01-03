<?php

declare(strict_types=1);

namespace Tenanted\Core\Resolvers;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Routing\RouteRegistrar;
use Tenanted\Core\Contracts\Tenancy;
use Tenanted\Core\Contracts\Tenant;
use Tenanted\Core\Http\Middleware\SetTenantHeader;
use Tenanted\Core\Support\BaseIdentityResolver;

/**
 * Header Identity Resolver
 *
 * This implementation of {@see \Tenanted\Core\Contracts\IdentityResolver}, uses
 * a HTTP header as a tenant identifier.
 */
class HeaderIdentityResolver extends BaseIdentityResolver
{
    /**
     * The name of the header
     *
     * @var string
     */
    private string $header;

    /**
     * @param string $name
     * @param string $header
     */
    public function __construct(string $name, string $header)
    {
        parent::__construct($name);

        $this->header = $header;
    }

    /**
     * Get the name of the HTTP header
     *
     * @return string
     */
    public function header(): string
    {
        return $this->header;
    }

    /**
     * @param \Illuminate\Http\Request         $request
     * @param \Tenanted\Core\Contracts\Tenancy $tenancy
     *
     * @return string|false|null
     */
    public function resolve(Request $request, Tenancy $tenancy): string|null|false
    {
        if (! $request->hasHeader($this->header())) {
            return false;
        }

        $identifier = $request->header($this->header());

        if (! is_string($identifier)) {
            return false;
        }

        return $identifier;
    }

    /**
     * @param \Illuminate\Routing\Router               $router
     * @param string                                   $tenancy
     * @param \Closure|\Closure[]|string|string[]|null $routes
     *
     * @return \Illuminate\Routing\RouteRegistrar
     */
    public function routes(Router $router, string $tenancy, array|Closure|string|null $routes = null): RouteRegistrar
    {
        return parent::routes($router, $tenancy, $routes)
            ->middleware(SetTenantHeader::ALIAS . ':' . $tenancy . ',' . $this->name());
    }

    /**
     * @param \Tenanted\Core\Contracts\Tenancy     $tenancy
     * @param \Tenanted\Core\Contracts\Tenant|null $tenant
     *
     * @return void
     */
    public function setup(Tenancy $tenancy, ?Tenant $tenant = null): void
    {
        // There is nothing to do when setting up a header-based identity
        // resolver
    }
}
