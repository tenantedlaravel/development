<?php
declare(strict_types=1);

namespace Tenanted\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tenanted\Core\Exceptions\IdentityResolverException;
use Tenanted\Core\Resolvers\HeaderIdentityResolver;
use Tenanted\Core\TenantedManager;

/**
 * Set Tenant Header Middleware
 *
 * When using the 'header' identity resolver
 * ({@see \Tenanted\Core\Resolvers\HeaderIdentityResolver}), this middleware
 * will add a header to the response containing the current tenant identifier,
 * unless otherwise specified.
 */
class SetTenantHeader
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
     * @throws \Tenanted\Core\Exceptions\TenancyException
     * @throws \Tenanted\Core\Exceptions\TenantProviderException
     * @throws \Tenanted\Core\Exceptions\IdentityResolverException
     */
    public function handle(Request $request, Closure $next, ?string $tenancyName = null, ?string $resolverName = null): Response
    {
        $tenancy = $this->manager->tenancy($tenancyName);

        if ($tenancy->check() && $tenancy->wasIdentified() && $tenancy->identifiedBy() !== $resolverName) {
            $resolver = $this->manager->resolver($resolverName);

            if (! ($resolver instanceof HeaderIdentityResolver)) {
                throw new IdentityResolverException('Current resolver \'' . $resolver->name() . '\' is not an instance of \'' . HeaderIdentityResolver::class . '\'');
            }

            $response = $next($request);
            $response->headers->add([$resolver->header() => $tenancy->identifier()]);

            return $response;
        }

        return $next($request);
    }
}