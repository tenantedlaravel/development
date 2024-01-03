<?php

declare(strict_types=1);

namespace Tenanted\Core\Exceptions;

use Exception;

final class TenantProviderException extends Exception
{
    public static function noDriver(string $name): TenantProviderException
    {
        return new self('No driver specified for tenant provider \'' . $name . '\'');
    }

    public static function missingConfig(string $name, string $config): TenantProviderException
    {
        return new self('Missing config value \'' . $config . '\' for tenant provider \'' . $name . '\'');
    }

    public static function unknown(string $name): TenantProviderException
    {
        return new self('Unable to create a tenant provider \'' . $name . '\'');
    }

    /**
     * @param string              $name
     * @param string              $config
     * @param array<class-string> $classes
     *
     * @return \Tenanted\Core\Exceptions\TenantProviderException
     */
    public static function invalidClass(string $name, string $config, array $classes): TenantProviderException
    {
        return new self('Config value \'' . $config . '\' for tenant provider \'' . $name . '\' must be a child of the following: ' . implode(', ', $classes));
    }
}
