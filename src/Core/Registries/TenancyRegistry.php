<?php
declare(strict_types=1);

namespace Tenanted\Core\Registries;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Tenanted\Core\Contracts\Registry;
use Tenanted\Core\Contracts\Tenancy as TenancyContract;
use Tenanted\Core\Exceptions\TenancyException;
use Tenanted\Core\Support\BaseRegistry;
use Tenanted\Core\Tenancy;

/**
 * @extends \Tenanted\Core\Support\BaseRegistry<TenancyContract>
 */
class TenancyRegistry extends BaseRegistry
{
    /**
     * @var string|null
     */
    private ?string $default;

    /**
     * @var \Tenanted\Core\Contracts\Registry<\Tenanted\Core\Contracts\TenantProvider>
     */
    private Registry $providers;

    public function __construct(Application $app, Repository $config, Registry $providers, ?string $default = null)
    {
        parent::__construct($app, $config);

        $this->providers = $providers;
        $this->default   = $default;
    }

    /**
     * Get the default tenancy name
     *
     * @return string
     */
    private function getDefaultTenancyName(): string
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
    private function getTenancyConfig(string $name): array
    {
        return $this->config->get($name, []);
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
     * @return TenancyContract
     *
     * @throws \Tenanted\Core\Exceptions\TenancyException
     * @throws \Tenanted\Core\Exceptions\TenantProviderException
     */
    public function get(?string $name = null): TenancyContract
    {
        // Get the name of the default tenancy if one wasn't provided
        $name ??= $this->getDefaultTenancyName();

        // If we've already instantiated this tenancy, let's use that
        if (isset($this->instances[$name])) {
            return $this->instances[$name];
        }

        // Load the tenancy config
        $config = $this->getTenancyConfig($name);

        if (self::$customCreators[$name]) {
            // There's a tenancy creator for its name, so we'll use that to
            // create it
            $tenancy = self::$customCreators[$name]($config, $name);
        } else {
            // There's no custom tenancy creator, so we'll check to make sure
            // the config contains a driver
            if (! isset($config['driver'])) {
                throw TenancyException::noDriver($name);
            }

            // Get the driver
            $driver = $config['driver'];

            if (isset(self::$customCreators[$driver])) {
                // There's a tenancy creator for its driver, so we'll use that
                // to create it
                $tenancy = self::$customCreators[$driver]($config, $name);
            } else {
                // There's no custom tenancy creator for the driver, we'll
                // create one using the default implementation
                $tenancy = new Tenancy(
                    $name,
                    $this->providers->get($config['provider'] ?? null),
                    $config['options'] ?? []
                );
            }
        }

        if ($tenancy instanceof TenancyContract) {
            // A tenancy was created, so we'll store the instance and return it
            return $this->instances[$name] = $tenancy;
        }

        // We were unable to create a tenancy, which is a problem
        throw TenancyException::unknown($name);
    }
}