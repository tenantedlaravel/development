<?php

declare(strict_types=1);

namespace Tenanted\Core\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Tenanted\Core\Contracts\Tenancy;
use Tenanted\Core\Contracts\Tenant;

/**
 * Tenant Resolved Event
 *
 * This is a base event class used when tenants are loaded or identified.
 *
 * @psalm-suppress MoreSpecificImplementedParamType
 * @psalm-suppress MethodSignatureMismatch
 *
 * @method static array dispatch(Tenant $tenant, Tenancy $tenancy)
 * @method static array dispatchIf(bool $boolean, Tenant $tenant, Tenancy $tenancy)
 * @method static array dispatchUnless(bool $boolean, Tenant $tenant, Tenancy $tenancy)
 */
abstract class TenantResolved
{
    use Dispatchable;

    /**
     * @var \Tenanted\Core\Contracts\Tenant
     */
    private Tenant $tenant;

    /**
     * @var \Tenanted\Core\Contracts\Tenancy
     */
    private Tenancy $tenancy;

    public function __construct(Tenant $tenant, Tenancy $tenancy)
    {
        $this->tenant = $tenant;
        $this->tenancy = $tenancy;
    }

    public function tenant(): Tenant
    {
        return $this->tenant;
    }

    public function tenancy(): Tenancy
    {
        return $this->tenancy;
    }
}
