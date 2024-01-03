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

        /**
         * @var \Illuminate\Routing\Route $route
         */
        $route = $request->route();

        if (! $route->hasParameter($parameter)) {
            return false;
        }

        $identifier = $route->parameter($parameter);

        $route->forgetParameter($parameter);

        if (! is_string($identifier) && $identifier !== null) {
            return false;
        }

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
        /**
         * @phpstan-ignore-next-line
         * @psalm-suppress PossiblyUndefinedMethod
         */
        app(UrlGenerator::class)->defaults([$this->getParameterName($tenancy->name()) => $tenant?->getTenantIdentifier()]);
    }
}
