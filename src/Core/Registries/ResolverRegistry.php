<?php

declare(strict_types=1);

namespace Tenanted\Core\Registries;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Str;
use Tenanted\Core\Contracts\IdentityResolver;
use Tenanted\Core\Exceptions\IdentityResolverException;
use Tenanted\Core\Resolvers\HeaderIdentityResolver;
use Tenanted\Core\Resolvers\PathIdentityResolver;
use Tenanted\Core\Resolvers\SubdomainIdentityResolver;
use Tenanted\Core\Support\BaseRegistry;

/**
 * @extends \Tenanted\Core\Support\BaseRegistry<\Tenanted\Core\Contracts\IdentityResolver>
 */
class ResolverRegistry extends BaseRegistry
{
    /**
     * @var string
     */
    private string $default;

    public function __construct(Application $app, Repository $config, string $default)
    {
        parent::__construct($app, $config);

        $this->default = $default;
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
        /* @phpstan-ignore-next-line */
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
     *
     * @uses \Tenanted\Core\Registries\ResolverRegistry::createHeaderResolver()
     * @uses \Tenanted\Core\Registries\ResolverRegistry::createPathResolver()
     * @uses \Tenanted\Core\Registries\ResolverRegistry::createSubdomainResolver()
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

        if (isset(self::$customCreators[$name])) {
            // There's an identity resolver creator for its name, so we'll use
            // that to create it
            $resolver = self::$customCreators[$name]($config, $name);
        } else {
            // There's no custom identity resolver creator, so we'll check to
            // make sure the config contains a driver
            if (! isset($config['driver']) || ! is_string($config['driver'])) {
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
     * @param array<string, mixed> $config
     * @param string               $name
     *
     * @return \Tenanted\Core\Resolvers\PathIdentityResolver
     */
    private function createPathResolver(array $config, string $name): PathIdentityResolver
    {
        $segment = isset($config['segment']) && is_int($config['segment']) ? $config['segment'] : 0;

        return new PathIdentityResolver($name, max($segment, 0));
    }

    /**
     * Create a new instance of the header identity resolver
     *
     * @param array<string, mixed> $config
     * @param string               $name
     *
     * @return \Tenanted\Core\Resolvers\HeaderIdentityResolver
     *
     * @throws \Tenanted\Core\Exceptions\IdentityResolverException
     */
    private function createHeaderResolver(array $config, string $name): HeaderIdentityResolver
    {
        if (! isset($config['header']) || ! is_string($config['header'])) {
            throw IdentityResolverException::missingConfig($name, 'header');
        }

        return new HeaderIdentityResolver($name, $config['header']);
    }

    /**
     * Create a new instance of the subdomain identity resolver
     *
     * @param array<string, mixed> $config
     * @param string               $name
     *
     * @return \Tenanted\Core\Resolvers\SubdomainIdentityResolver
     *
     * @throws \Tenanted\Core\Exceptions\IdentityResolverException
     */
    private function createSubdomainResolver(array $config, string $name): SubdomainIdentityResolver
    {
        if (! isset($config['domain']) || ! is_string($config['domain'])) {
            throw IdentityResolverException::missingConfig($name, 'domain');
        }

        return new SubdomainIdentityResolver($name, $config['domain']);
    }
}
