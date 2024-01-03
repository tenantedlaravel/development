<?php

declare(strict_types=1);

namespace Tenanted\Core\Events;

use Tenanted\Core\Contracts\Tenancy;
use Tenanted\Core\Contracts\Tenant;

/**
 * Tenant Identified Event
 *
 * This event is fired when a tenant is identified using its identifier, before
 * they are set as the current tenant.
 *
 * @psalm-suppress MoreSpecificImplementedParamType
 * @psalm-suppress MethodSignatureMismatch
 *
 * @method static array dispatch(Tenant $tenant, Tenancy $tenancy, string $resolver)
 * @method static array dispatchIf(bool $boolean, Tenant $tenant, Tenancy $tenancy, string $resolver)
 * @method static array dispatchUnless(bool $boolean, Tenant $tenant, Tenancy $tenancy, string $resolver)
 */
final class TenantIdentified extends TenantResolved
{
    private string $resolver;

    public function __construct(Tenant $tenant, Tenancy $tenancy, string $resolver)
    {
        parent::__construct($tenant, $tenancy);

        $this->resolver = $resolver;
    }

    public function resolver(): string
    {
        return $this->resolver;
    }
}
