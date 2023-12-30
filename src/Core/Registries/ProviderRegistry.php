<?php
declare(strict_types=1);

namespace Tenanted\Core\Registries;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Str;
use Tenanted\Core\Contracts\TenantProvider;
use Tenanted\Core\Exceptions\TenantProviderException;
use Tenanted\Core\Providers\DatabaseTenantProvider;
use Tenanted\Core\Providers\EloquentTenantProvider;
use Tenanted\Core\Support\BaseRegistry;
use Tenanted\Core\Support\GenericTenant;

/**
 * @extends \Tenanted\Core\Support\BaseRegistry<\Tenanted\Core\Contracts\TenantProvider>
 */
class ProviderRegistry extends BaseRegistry
{
    /**
     * @var string|null
     */
    private ?string $default;

    public function __construct(Application $app, Repository $config, ?string $default = null)
    {
        parent::__construct($app, $config);
        $this->default = $default;
    }

    /**
     * Get the default tenant provider name
     *
     * @return string
     */
    private function getDefaultProviderName(): string
    {
        return $this->default;
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
        return $this->config->get($name, []);
    }

    /**
     * @param string $name
     *
     * @return \Tenanted\Core\Contracts\TenantProvider
     *
     * @throws \Tenanted\Core\Exceptions\TenantProviderException
     */
    public function get(string $name): TenantProvider
    {
        // Get the name of the default provider if one wasn't provided
        $name ??= $this->getDefaultProviderName();

        // If we've already instantiated this provider, let's use that
        if (isset($this->instances[$name])) {
            return $this->instances[$name];
        }

        // Set the provider variable and load the provider config
        $provider = null;
        $config   = $this->getProviderConfig($name);

        if (self::$customCreators[$name]) {
            // There's a provider creator for its name, so we'll use that to
            // create it
            $provider = self::$customCreators[$name]($config, $name);
        } else {
            // There's no custom provider creator, so we'll check to make sure
            // the config contains a driver
            if (! isset($config['driver'])) {
                throw TenantProviderException::noDriver($name);
            }

            // Get the driver
            $driver = $config['driver'];

            if (isset(self::$customCreators[$driver])) {
                // There's a provider creator for its driver, so we'll use that
                // to create it
                $provider = self::$customCreators[$driver]($config, $name);
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
            return $this->instances[$name] = $provider;
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
}