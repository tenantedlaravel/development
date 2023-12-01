<?php
declare(strict_types=1);

namespace Tenanted\Core\Exceptions;

use Exception;

final class TenancyException extends Exception
{
    public static function noDriver(string $name): TenancyException
    {
        return new self('No driver specified for tenancy \'' . $name . '\'');
    }

    public static function missingConfig(string $name, string $config): TenancyException
    {
        return new self('Missing config value \'' . $config . '\' for tenancy \'' . $name . '\'');
    }

    public static function unknown(string $name): TenancyException
    {
        return new self('Unable to create a tenancy \'' . $name . '\'');
    }
}