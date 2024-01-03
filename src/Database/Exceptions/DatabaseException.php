<?php

declare(strict_types=1);

namespace Tenanted\Database\Exceptions;

use Tenanted\Core\Exceptions\TenantedException;

abstract class DatabaseException extends TenantedException
{
}
