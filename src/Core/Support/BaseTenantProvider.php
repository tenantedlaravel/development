<?php

declare(strict_types=1);

namespace Tenanted\Core\Support;

use Tenanted\Core\Contracts\TenantProvider;

/**
 * Base Tenant Provider
 *
 * This tenant provider provides a shared base for all other tenant providers.
 */
abstract class BaseTenantProvider implements TenantProvider
{
    /**
     * The tenant provider name
     *
     * @var string
     */
    private string $name;

    /**
     * Create a new base tenant provider instance
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Get the name of the tenant provider
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }
}
