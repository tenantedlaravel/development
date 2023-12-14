<?php

namespace Tenanted\Core\Contracts;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Routing\RouteRegistrar;
use Tenanted\Core\Support\IdentificationStage;

/**
 * Identity Resolver Contract
 *
 * This contract represents the identity resolver, a class responsible for
 * resolving a tenants' identifier for a given request.
 */
interface IdentityResolver
{

    /**
     * Resolve the identifier for a given request, for a given tenancy
     *
     * @param \Illuminate\Http\Request         $request
     * @param \Tenanted\Core\Contracts\Tenancy $tenancy
     *
     * @return string|null|false
     *
     * @throws \Tenanted\Core\Exceptions\IdentityResolverException
     */
    public function resolve(Request $request, Tenancy $tenancy): string|null|false;

    /**
     * Register routes to use this identity resolver
     *
     * @param \Illuminate\Routing\Router $router
     * @param string                     $tenancy
     * @param \Closure|array|string|null $routes
     *
     * @return RouteRegistrar
     */
    public function routes(Router $router, string $tenancy, Closure|array|string|null $routes = null): RouteRegistrar;

    /**
     * Perform setup actions for a tenancies tenancy changed
     *
     * @param \Tenanted\Core\Contracts\Tenancy     $tenancy
     * @param \Tenanted\Core\Contracts\Tenant|null $tenant
     *
     * @return void
     */
    public function setup(Tenancy $tenancy, ?Tenant $tenant = null): void;

    /**
     * Get the name of this identity resolver
     *
     * Returns the name that this identity resolver was registered under.
     *
     * @return string
     */
    public function name(): string;
}