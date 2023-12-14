<?php
declare(strict_types=1);

namespace Tenanted\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tenanted\Core\Exceptions\TenantNotFoundException;
use Tenanted\Core\TenantedManager;

/**
 * Tenanted Route Middleware
 *
 * This middleware sits and acts as a marker for tenant identification, and
 * makes sure that there is an active tenant.
 */
class TenantedRoute
{
    /**
     * @var \Tenanted\Core\TenantedManager
     */
    private TenantedManager $manager;

    public function __construct(TenantedManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request                                                         $request
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     * @param string|null                                                                      $tenancyName
     * @param string|null                                                                      $resolverName
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Tenanted\Core\Exceptions\IdentityResolverException
     * @throws \Tenanted\Core\Exceptions\TenancyException
     * @throws \Tenanted\Core\Exceptions\TenantNotFoundException
     * @throws \Tenanted\Core\Exceptions\TenantProviderException
     */
    public function handle(Request $request, Closure $next, ?string $tenancyName = null, ?string $resolverName = null): Response
    {
        $tenancy  = $this->manager->tenancy($tenancyName);
        $resolver = $this->manager->resolver($resolverName);

        // If there's no tenant, it's exception time
        if (! $tenancy->check()) {
            throw TenantNotFoundException::missing($tenancy->name(), $resolver->name());
        }

        // If the current tenant wasn't identified, or wasn't identified by the
        // expected resolver, it's also exception time
        if ($tenancy->wasIdentified() && $tenancy->identifiedBy() !== $resolver->name()) {
            throw TenantNotFoundException::invalidResolver($tenancy->name(), $resolver->name());
        }

        return $next($request);
    }
}