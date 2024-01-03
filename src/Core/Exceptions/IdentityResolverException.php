<?php

declare(strict_types=1);

namespace Tenanted\Core\Exceptions;

final class IdentityResolverException extends TenantedException
{
    public static function noDriver(string $name): IdentityResolverException
    {
        return new self('No driver specified for identity resolver \'' . $name . '\'');
    }

    public static function missingConfig(string $name, string $config): IdentityResolverException
    {
        return new self('Missing config value \'' . $config . '\' for identity resolver \'' . $name . '\'');
    }

    public static function unknown(string $name): IdentityResolverException
    {
        return new self('Unable to create a identity resolver \'' . $name . '\'');
    }
}
