<?php
declare(strict_types=1);

namespace Tenanted\Core\Support;

use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Str;
use Tenanted\Core\Contracts\Tenancy;
use Tenanted\Core\Contracts\Tenant;

/**
 * Parameter Identity Resolver
 *
 * A base abstract implementation of the {@see \Tenanted\Core\Contracts\IdentityResolver}
 * contract for identity resolvers that use route parameters.
 */
abstract class ParameterIdentityResolver extends BaseIdentityResolver
{
    /**
     * Get the parameter name based on the tenancy
     *
     * @param string $tenancy
     *
     * @return string
     */
    protected function getParameterName(string $tenancy): string
    {
        return Str::slug($tenancy . '_' . $this->name());
    }

    /**
     * @param \Illuminate\Http\Request         $request
     * @param \Tenanted\Core\Contracts\Tenancy $tenancy
     *
     * @return string|false|null
     */
    public function resolve(Request $request, Tenancy $tenancy): string|null|false
    {
        if ($request->route() === null) {
            return false;
        }

        $parameter = $this->getParameterName($tenancy->name());

        if (! $request->route()->hasParameter($parameter)) {
            return false;
        }

        $identifier = $request->route()->parameter($parameter);

        $request->route()->forgetParameter($parameter);

        return $identifier;
    }

    /**
     * @param \Tenanted\Core\Contracts\Tenancy     $tenancy
     * @param \Tenanted\Core\Contracts\Tenant|null $tenant
     *
     * @return void
     */
    public function setup(Tenancy $tenancy, ?Tenant $tenant = null): void
    {
        app(UrlGenerator::class)->defaults([$this->getParameterName($tenancy->name()) => $tenant?->getTenantIdentifier(),]);
    }
}