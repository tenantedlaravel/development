<?php
declare(strict_types=1);

namespace Tenanted\Core\Registries;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Str;
use Tenanted\Core\Contracts\IdentityResolver;
use Tenanted\Core\Contracts\Registry;
use Tenanted\Core\Contracts\Tenancy as TenancyContract;
use Tenanted\Core\Exceptions\IdentityResolverException;
use Tenanted\Core\Exceptions\TenancyException;
use Tenanted\Core\Resolvers\HeaderIdentityResolver;
use Tenanted\Core\Resolvers\PathIdentityResolver;
use Tenanted\Core\Resolvers\SubdomainIdentityResolver;
use Tenanted\Core\Support\BaseRegistry;
use Tenanted\Core\Tenancy;

/**
 * @extends \Tenanted\Core\Support\BaseRegistry<\Tenanted\Core\Contracts\IdentityResolver>
 */
class ResolverRegistry extends BaseRegistry
{
    /**
     * @var string|null
     */
    private ?string $default;

    public function __construct(Application $app, Repository $config, ?string $default = null)
    {
        parent::__construct($app, $config);

        $this->default   = $default;
    }

    /**
     * Get the default identity resolver name
     *
     * @return string
     */
    private function getDefaultResolverName(): string
    {
        return $this->default;
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
        return $this->config->get($name, []);
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
    public function get(?string $name = null): IdentityResolver
    {
        $name ??= $this->getDefaultResolverName();

        if (isset($this->instances[$name])) {
            return $this->instances[$name];
        }

        // Set the identity resolver variable and load the tenancy config
        $resolver = null;
        $config   = $this->getResolverConfig($name);

        if (self::$customCreators[$name]) {
            // There's an identity resolver creator for its name, so we'll use
            // that to create it
            $resolver = self::$customCreators[$name]($config, $name);
        } else {
            // There's no custom identity resolver creator, so we'll check to
            // make sure the config contains a driver
            if (! isset($config['driver'])) {
                throw IdentityResolverException::noDriver($name);
            }

            // Get the driver
            $driver = $config['driver'];

            if (isset(self::$customCreators[$driver])) {
                // There's an identity resolver creator for its driver, so we'll
                // use that to create it
                $resolver = self::$customCreators[$driver]($config, $name);
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
            return $this->instances[$name] = $resolver;
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
    private function createPathResolver(array $config, string $name): PathIdentityResolver
    {
        return new PathIdentityResolver($name, (int)($config['segment'] ?? 0));
    }

    /**
     * Create a new instance of the header identity resolver
     *
     * @param array  $config
     * @param string $name
     *
     * @return \Tenanted\Core\Resolvers\HeaderIdentityResolver
     */
    private function createHeaderResolver(array $config, string $name): HeaderIdentityResolver
    {
        return new HeaderIdentityResolver($name, $config['header']);
    }

    /**
     * Create a new instance of the subdomain identity resolver
     *
     * @param array  $config
     * @param string $name
     *
     * @return \Tenanted\Core\Resolvers\SubdomainIdentityResolver
     */
    private function createSubdomainResolver(array $config, string $name): SubdomainIdentityResolver
    {
        return new SubdomainIdentityResolver($name, $config['domain']);
    }
}