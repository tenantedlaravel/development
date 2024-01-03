<?php

declare(strict_types=1);

namespace Tenanted\Core;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Tenanted\Core\Contracts\Registry;
use Tenanted\Core\Registries\ProviderRegistry;
use Tenanted\Core\Registries\ResolverRegistry;
use Tenanted\Core\Registries\TenancyRegistry;

/**
 * Tenanted Manager
 *
 * The central management class for the tenanted package.
 */
final class TenantedManager
{
    /**
     * The Laravel application
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    private Application $app;

    /**
     * The tenanted config
     *
     * @var \Illuminate\Config\Repository
     */
    private Repository $config;

    /**
     * @var \Tenanted\Core\Contracts\Registry<\Tenanted\Core\Contracts\TenantProvider>
     */
    private Registry $providers;

    /**
     * @var \Tenanted\Core\Contracts\Registry<\Tenanted\Core\Contracts\Tenancy>
     */
    private Registry $tenancies;

    /**
     * @var \Tenanted\Core\Contracts\Registry<\Tenanted\Core\Contracts\IdentityResolver>
     */
    private Registry $resolvers;

    /**
     * @var \Tenanted\Core\Contracts\Tenancy|null
     */
    private ?Contracts\Tenancy $currentTenancy;

    /**
     * Create a new instance of the tenanted manager
     *
     * @param \Illuminate\Contracts\Foundation\Application                                      $app
     * @param \Tenanted\Core\Contracts\Registry<\Tenanted\Core\Contracts\TenantProvider>|null   $providers
     * @param \Tenanted\Core\Contracts\Registry<\Tenanted\Core\Contracts\Tenancy>|null          $tenancies
     * @param \Tenanted\Core\Contracts\Registry<\Tenanted\Core\Contracts\IdentityResolver>|null $resolvers
     */
    public function __construct(Application $app, ?Registry $providers = null, ?Registry $tenancies = null, ?Registry $resolvers = null)
    {
        $this->app = $app;

        if ($providers !== null) {
            $this->providers = $providers;
        }

        if ($tenancies !== null) {
            $this->tenancies = $tenancies;
        }

        if ($resolvers !== null) {
            $this->resolvers = $resolvers;
        }
    }

    /**
     * Load the tenanted config
     *
     * @return void
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function loadConfig(): void
    {
        /* @phpstan-ignore-next-line */
        $this->config = new Repository($this->app->make('config')->get('tenanted', []));
    }

    /**
     * Get the tenanted package config
     *
     * @return \Illuminate\Config\Repository
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function config(): Repository
    {
        if (! isset($this->config)) {
            $this->loadConfig();
        }

        return $this->config;
    }

    /**
     * @return \Tenanted\Core\Contracts\Registry<\Tenanted\Core\Contracts\TenantProvider>
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function providers(): Registry
    {
        if (! isset($this->providers)) {
            $this->providers = new ProviderRegistry(
                $this->app,
                new Repository($this->config()->get('providers')),
                $this->config()->get('defaults.provider')/* @phpstan-ignore-line */
            );
        }

        return $this->providers;
    }

    /**
     * @return \Tenanted\Core\Contracts\Registry<\Tenanted\Core\Contracts\Tenancy>
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function tenancies(): Registry
    {
        if (! isset($this->tenancies)) {
            $this->tenancies = new TenancyRegistry(
                $this->app,
                new Repository($this->config()->get('tenancies')),
                $this->providers(),
                $this->config()->get('defaults.tenancy')/* @phpstan-ignore-line */
            );
        }

        return $this->tenancies;
    }

    /**
     * @return \Tenanted\Core\Contracts\Registry<\Tenanted\Core\Contracts\IdentityResolver>
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function resolvers(): Registry
    {
        if (! isset($this->tenancies)) {
            $this->resolvers = new ResolverRegistry(
                $this->app,
                new Repository($this->config()->get('resolvers')),
                $this->config()->get('defaults.resolver') /* @phpstan-ignore-line */
            );
        }

        return $this->resolvers;
    }

    /**
     * Set the current tenancy
     *
     * @param \Tenanted\Core\Contracts\Tenancy|null $tenancy
     *
     * @return static
     */
    public function setCurrentTenancy(?Contracts\Tenancy $tenancy): self
    {
        $this->currentTenancy = $tenancy;

        return $this;
    }

    /**
     * Get the current tenancy
     *
     * @return \Tenanted\Core\Contracts\Tenancy|null
     */
    public function currentTenancy(): ?Contracts\Tenancy
    {
        return $this->currentTenancy;
    }

    /**
     * Perform tenant identification for the request
     *
     * @param \Illuminate\Http\Request $request
     * @param string|null              $tenancyName
     * @param string|null              $resolverName
     *
     * @return bool
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Tenanted\Core\Exceptions\IdentityResolverException
     */
    public function identify(Request $request, ?string $tenancyName = null, ?string $resolverName = null): bool
    {
        // Grab the tenancy for this identification
        $tenancy = $this->tenancies()->get($tenancyName);

        // Set the current tenancy
        $this->setCurrentTenancy($tenancy);

        // Grab the resolver and then resolver the identifier
        $resolver   = $this->resolvers()->get($resolverName);
        $identifier = $resolver->resolve($request, $tenancy);

        if ($identifier) {
            // If there's an identifier, we'll perform the identification
            return $tenancy->identify($identifier, $resolver->name());
        }

        return false;
    }
}
