<?php
declare(strict_types=1);

namespace Tenanted\Core;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Str;
use Tenanted\Core\Contracts\IdentityResolver;
use Tenanted\Core\Contracts\TenantProvider;
use Tenanted\Core\Exceptions\IdentityResolverException;
use Tenanted\Core\Exceptions\TenancyException;
use Tenanted\Core\Exceptions\TenantProviderException;
use Tenanted\Core\Providers\DatabaseTenantProvider;
use Tenanted\Core\Providers\EloquentTenantProvider;
use Tenanted\Core\Resolvers\PathIdentityResolver;
use Tenanted\Core\Support\GenericTenant;

final class TenantedManager
{
    /**
     * Custom tenant provider creators
     *
     * @var array<string, callable(array<string, mixed>, string): \Tenanted\Core\Contracts\TenantProvider>
     */
    private static array $customProviderCreators = [];

    /**
     * Custom tenancy creators
     *
     * @var array<string, callable(array<string, mixed>, string): \Tenanted\Core\Contracts\Tenancy
     */
    private static array $customTenancyCreators = [];

    /**
     * Custom identity resolver creators
     *
     * @var array<string, callable(array<string, mixed>, string): \Tenanted\Core\Contracts\IdentityResolver
     */
    private static array $customResolverCreators = [];

    /**
     * Register a custom tenant provider creator
     *
     * @param string                                                                          $name
     * @param callable(array<string, mixed>, string): \Tenanted\Core\Contracts\TenantProvider $creator
     *
     * @return void
     */
    public static function registerProvider(string $name, callable $creator): void
    {
        self::$customProviderCreators[$name] = $creator;
    }

    /**
     * Register a custom tenancy creator
     *
     * @param string                                                                          $name
     * @param callable(array<string, mixed>, string): \Tenanted\Core\Contracts\TenantProvider $creator
     *
     * @return void
     */
    public static function registerTenancy(string $name, callable $creator): void
    {
        self::$customTenancyCreators[$name] = $creator;
    }

    /**
     * Register a custom identity resolver creator
     *
     * @param string                                                                            $name
     * @param callable(array<string, mixed>, string): \Tenanted\Core\Contracts\IdentityResolver $creator
     *
     * @return void
     */
    public static function registerResolver(string $name, callable $creator): void
    {
        self::$customResolverCreators[$name] = $creator;
    }

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
     * Tenant provider instances
     *
     * @var array<string, \Tenanted\Core\Contracts\TenantProvider>
     */
    private array $providers = [];

    /**
     * Tenancy instances
     *
     * @var array<string, \Tenanted\Core\Contracts\Tenancy>
     */
    private array $tenancies = [];

    /**
     * Identity resolver instances
     *
     * @var array<string, \Tenanted\Core\Contracts\IdentityResolver>
     */
    private array $resolvers = [];

    /**
     * @var \Tenanted\Core\Contracts\Tenancy|null
     */
    private ?Contracts\Tenancy $currentTenancy;

    /**
     * Create a new instance of the tenanted manager
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Load the tenanted config
     *
     * @return void
     */
    public function loadConfig(): void
    {
        $this->config = new Repository($this->app['config']['tenanted'] ?? []);
    }

    /**
     * Get the tenanted package config
     *
     * @return \Illuminate\Config\Repository
     */
    private function config(): Repository
    {
        if (! isset($this->config)) {
            $this->loadConfig();
        }

        return $this->config;
    }

    /**
     * Get the default tenant provider name
     *
     * @return string
     */
    private function getDefaultProviderName(): string
    {
        return $this->config()->get('defaults.provider');
    }

    /**
     * Get the tenant provider config
     *
     * @param string $name
     *
     * @return array<string, mixed>
     */
    private function getProviderConfig(string $name): array
    {
        return $this->config()->get('providers.' . $name, []);
    }

    /**
     * Get a tenant provider
     *
     * Returns a new or previously created tenant provider based on its name and
     * driver. If no name is provided, the configured default will be used.
     *
     * This method will try to resolve a tenant provider in the following order:
     *
     *   - Previously created tenant provider by name
     *   - Custom tenant provider creator by name
     *   - Custom tenant provider creator by driver
     *   - First-party method by driver
     *
     * @param string|null $name
     *
     * @return \Tenanted\Core\Contracts\TenantProvider
     *
     * @throws \Tenanted\Core\Exceptions\TenantProviderException
     */
    public function provider(?string $name = null): TenantProvider
    {
        // Get the name of the default provider if one wasn't provided
        $name ??= $this->getDefaultProviderName();

        // If we've already instantiated this provider, let's use that
        if (isset($this->providers[$name])) {
            return $this->providers[$name];
        }

        // Set the provider variable and load the provider config
        $provider = null;
        $config   = $this->getProviderConfig($name);

        if (self::$customProviderCreators[$name]) {
            // There's a provider creator for its name, so we'll use that to
            // create it
            $provider = self::$customProviderCreators[$name]($config, $name);
        } else {
            // There's no custom provider creator, so we'll check to make sure
            // the config contains a driver
            if (! isset($config['driver'])) {
                throw TenantProviderException::noDriver($name);
            }

            // Get the driver
            $driver = $config['driver'];

            if (isset(self::$customProviderCreators[$driver])) {
                // There's a provider creator for its driver, so we'll use that
                // to create it
                $provider = self::$customProviderCreators[$driver]($config, $name);
            } else {
                // There's no custom provider creator for the driver, so we'll
                // see if it's a first party provider supported directly by
                // this class
                $method = 'create' . Str::ucfirst($driver) . 'Provider';

                if (method_exists($this, $method)) {
                    // A method exists to create this type of provider, so let's do so
                    $provider = $this->$method($config, $name);
                }
            }
        }

        if ($provider instanceof TenantProvider) {
            // A provider was created, so we'll store the instance and return it
            return $this->providers[$name] = $provider;
        }

        // We were unable to create a tenant provider, which is a problem
        throw TenantProviderException::unknown($name);
    }

    /**
     * Create a new instance of the eloquent tenant provider
     *
     * @param array<string, mixed> $config
     * @param string               $name
     *
     * @return \Tenanted\Core\Providers\EloquentTenantProvider
     *
     * @throws \Tenanted\Core\Exceptions\TenantProviderException
     */
    private function createEloquentProvider(array $config, string $name): EloquentTenantProvider
    {
        if (! isset($config['model'])) {
            throw TenantProviderException::missingConfig($name, 'model');
        }

        return new EloquentTenantProvider($name, $config['model']);
    }

    /**
     * Create a new instance of the database tenant provider
     *
     * @param array<string, mixed> $config
     * @param string               $name
     *
     * @return \Tenanted\Core\Providers\DatabaseTenantProvider
     *
     * @throws \Tenanted\Core\Exceptions\TenantProviderException
     */
    private function createDatabaseProvider(array $config, string $name): DatabaseTenantProvider
    {
        if (! isset($config['table'])) {
            throw TenantProviderException::missingConfig($name, 'table');
        }

        return new DatabaseTenantProvider(
            $name,
            $this->app['db']->connection($config['connection'] ?? null),
            $config['table'],
            $config['identifier'] ?? 'identifier',
            $config['key'] ?? 'id',
            $config['entity'] ?? GenericTenant::class
        );
    }

    /**
     * Get the default tenant provider name
     *
     * @return string
     */
    private function getDefaultTenancyName(): string
    {
        return $this->config()->get('defaults.tenancy');
    }

    /**
     * Get the tenant provider config
     *
     * @param string $name
     *
     * @return array<string, mixed>
     */
    private function getTenancyConfig(string $name): array
    {
        return $this->config()->get('tenancies.' . $name, []);
    }

    /**
     * Get a tenant provider
     *
     * Returns a new or previously created tenant provider based on its name and
     * driver. If no name is provided, the configured default will be used.
     *
     * This method will try to resolve a tenant provider in the following order:
     *
     *   - Previously created tenant provider by name
     *   - Custom tenant provider creator by name
     *   - Custom tenant provider creator by driver
     *   - Default implementation
     *
     * @param string|null $name
     *
     * @return \Tenanted\Core\Contracts\Tenancy
     *
     * @throws \Tenanted\Core\Exceptions\TenancyException
     * @throws \Tenanted\Core\Exceptions\TenantProviderException
     */
    public function tenancy(?string $name = null): Contracts\Tenancy
    {
        // Get the name of the default tenancy if one wasn't provided
        $name ??= $this->getDefaultTenancyName();

        // If we've already instantiated this tenancy, let's use that
        if (isset($this->tenancies[$name])) {
            return $this->tenancies[$name];
        }

        // Load the tenancy config
        $config = $this->getTenancyConfig($name);

        if (self::$customTenancyCreators[$name]) {
            // There's a tenancy creator for its name, so we'll use that to
            // create it
            $tenancy = self::$customTenancyCreators[$name]($config, $name);
        } else {
            // There's no custom tenancy creator, so we'll check to make sure
            // the config contains a driver
            if (! isset($config['driver'])) {
                throw TenancyException::noDriver($name);
            }

            // Get the driver
            $driver = $config['driver'];

            if (isset(self::$customTenancyCreators[$driver])) {
                // There's a tenancy creator for its driver, so we'll use that
                // to create it
                $tenancy = self::$customTenancyCreators[$driver]($config, $name);
            } else {
                // There's no custom tenancy creator for the driver, we'll
                // create one using the default implementation
                $tenancy = new Tenancy($name, $this->provider($config['provider'] ?? null), $config['options'] ?? []);
            }
        }

        if ($tenancy instanceof Contracts\Tenancy) {
            // A tenancy was created, so we'll store the instance and return it
            return $this->tenancies[$name] = $tenancy;
        }

        // We were unable to create a tenancy, which is a problem
        throw TenancyException::unknown($name);
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
     * Get the default identity resolver name
     *
     * @return string
     */
    private function getDefaultResolverName(): string
    {
        return $this->config()->get('defaults.resolver');
    }

    /**
     * Get the identity resolver config
     *
     * @param string $name
     *
     * @return array<string, mixed>
     */
    private function getResolverConfig(string $name): array
    {
        return $this->config()->get('resolvers.' . $name, []);
    }

    /**
     * Get an identity resolver
     *
     * @param string|null $name
     *
     * @return \Tenanted\Core\Contracts\IdentityResolver
     *
     * @throws \Tenanted\Core\Exceptions\IdentityResolverException
     */
    public function resolver(?string $name = null): IdentityResolver
    {
        $name ??= $this->getDefaultResolverName();

        if (isset($this->resolvers[$name])) {
            return $this->resolvers[$name];
        }

        // Set the identity resolver variable and load the tenancy config
        $resolver = null;
        $config   = $this->getResolverConfig($name);

        if (self::$customResolverCreators[$name]) {
            // There's an identity resolver creator for its name, so we'll use
            // that to create it
            $resolver = self::$customResolverCreators[$name]($config, $name);
        } else {
            // There's no custom identity resolver creator, so we'll check to
            // make sure the config contains a driver
            if (! isset($config['driver'])) {
                throw IdentityResolverException::noDriver($name);
            }

            // Get the driver
            $driver = $config['driver'];

            if (isset(self::$customResolverCreators[$driver])) {
                // There's an identity resolver creator for its driver, so we'll
                // use that to create it
                $resolver = self::$customResolverCreators[$driver]($config, $name);
            } else {
                // There's no custom identity resolver creator for the driver,
                // so we'll see if it's a first party identity resolver
                // supported directly by this class
                $method = 'create' . Str::ucfirst($driver) . 'Resolver';

                if (method_exists($this, $method)) {
                    // A method exists to create this type of identity resolver,
                    // so let's do so
                    $resolver = $this->$method($config, $name);
                }
            }
        }

        if ($resolver instanceof IdentityResolver) {
            // An identity resolver was created, so we'll store the instance and return it
            return $this->resolvers[$name] = $resolver;
        }

        // We were unable to create an identity resolver, which is a problem
        throw IdentityResolverException::unknown($name);
    }

    /**
     * Create a new instance of the path identity resolver
     *
     * @param array  $config
     * @param string $name
     *
     * @return \Tenanted\Core\Resolvers\PathIdentityResolver
     */
    public function createPathResolver(array $config, string $name): PathIdentityResolver
    {
        return new PathIdentityResolver($name, (int)($config['segment'] ?? 0));
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
     * @throws \Tenanted\Core\Exceptions\IdentityResolverException
     * @throws \Tenanted\Core\Exceptions\TenancyException
     * @throws \Tenanted\Core\Exceptions\TenantProviderException
     */
    public function identify(Request $request, ?string $tenancyName = null, ?string $resolverName = null): bool
    {
        // Grab the tenancy for this identification
        $tenancy = $this->tenancy($tenancyName);

        // Set the current tenancy
        $this->setCurrentTenancy($tenancy);

        // Grab the resolver and then resolver the identifier
        $resolver   = $this->resolver($resolverName);
        $identifier = $resolver->resolve($request, $tenancy);

        if ($identifier) {
            // If there's an identifier, we'll perform the identification
            return $tenancy->identify($identifier, $resolver->name());
        }

        return false;
    }
}