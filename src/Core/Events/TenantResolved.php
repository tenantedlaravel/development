<?php
declare(strict_types=1);

namespace Tenanted\Core\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Tenanted\Core\Contracts\Tenant;

/**
 * @method static array dispatch(Tenant $tenant)
 * @method static array dispatchIf(bool $boolean, Tenant $tenant)
 * @method static array dispatchUnless(bool $boolean, Tenant $tenant)
 */
abstract class TenantResolved
{
    use Dispatchable;

    /**
     * @var \Tenanted\Core\Contracts\Tenant
     */
    private Tenant $tenant;

    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
    }

    public function tenant(): Tenant
    {
        return $this->tenant;
    }
}