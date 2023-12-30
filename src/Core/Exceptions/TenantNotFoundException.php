<?php
declare(strict_types=1);

namespace Tenanted\Core\Exceptions;

class TenantNotFoundException extends TenantedException
{
    public static function none(string $tenancy): TenantNotFoundException
    {
        return new self('There is no current tenant for the \'' . $tenancy . '\' tenancy');
    }

    public static function missing(string $tenancy, string $resolver): TenantNotFoundException
    {
        return new self('The identity resolver \'' . $resolver . '\' was unable to resolve a tenant for the \'' . $tenancy . '\' tenancy');
    }

    public static function invalidResolver(string $tenancy, string $resolver): TenantNotFoundException
    {
        return new self('The tenant for the \'' . $tenancy . '\' tenancy was not resolved using the \'' . $resolver . '\' resolver');
    }
}