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
}