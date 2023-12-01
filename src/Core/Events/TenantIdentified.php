<?php
declare(strict_types=1);

namespace Tenanted\Core\Events;

use Tenanted\Core\Contracts\Tenant;

/**
 * @method static array dispatch(Tenant $tenant, string $resolver)
 * @method static array dispatchIf(bool $boolean, Tenant $tenant, string $resolver)
 * @method static array dispatchUnless(bool $boolean, Tenant $tenant, string $resolver)
 */
final class TenantIdentified extends TenantResolved
{
    private string $resolver;

    public function __construct(Tenant $tenant, string $resolver)
    {
        parent::__construct($tenant);

        $this->resolver = $resolver;
    }

    public function resolver(): string
    {
        return $this->resolver;
    }
}