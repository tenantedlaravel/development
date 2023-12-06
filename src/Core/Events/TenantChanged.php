<?php
declare(strict_types=1);

namespace Tenanted\Core\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Tenanted\Core\Contracts\Tenancy;
use Tenanted\Core\Contracts\Tenant;

/**
 * @method static array dispatch(Tenancy $tenancy, ?Tenant $current, ?Tenant $new)
 * @method static array dispatchIf(bool $boolean, Tenancy $tenancy, ?Tenant $current, ?Tenant $new)
 * @method static array dispatchUnless(bool $boolean, Tenancy $tenancy, ?Tenant $current, ?Tenant $new)
 */
final class TenantChanged
{
    use Dispatchable;

    /**
     * @var \Tenanted\Core\Contracts\Tenancy
     */
    private Tenancy $tenancy;

    /**
     * @var \Tenanted\Core\Contracts\Tenant|null
     */
    private ?Tenant $current;

    /**
     * @var \Tenanted\Core\Contracts\Tenant|null
     */
    private ?Tenant                          $new;

    public function __construct(Tenancy $tenancy, ?Tenant $current, ?Tenant $new)
    {
        $this->current = $current;
        $this->new     = $new;
        $this->tenancy = $tenancy;
    }

    public function current(): ?Tenant
    {
        return $this->current;
    }

    public function new(): ?Tenant
    {
        return $this->new;
    }

    public function tenancy(): Tenancy
    {
        return $this->tenancy;
    }
}