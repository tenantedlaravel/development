<?php

declare(strict_types=1);

namespace Tenanted\Core\Contracts;

/**
 * Tenant Provider Contract
 *
 * This contract represents the basic requirements of a tenant provider. Tenant
 * providers are responsible for providing instances of {@see \Tenanted\Core\Contracts\Tenant}
 * for a given identifier or key.
 */
interface TenantProvider
{
    /**
     * Retrieve a tenant by an identifier
     *
     * Returns the {@see \Tenanted\Core\Contracts\Tenant} that has the provided
     * identifier, or null if none are found.
     *
     * @param string $identifier
     *
     * @return \Tenanted\Core\Contracts\Tenant|null
     */
    public function retrieveByIdentifier(string $identifier): ?Tenant;

    /**
     * Retrieve a tenant by a key
     *
     * Returns the {@see \Tenanted\Core\Contracts\Tenant} that has the provided
     * key, or null if none are found.
     *
     * @param int|string $key
     *
     * @return \Tenanted\Core\Contracts\Tenant|null
     */
    public function retrieveByKey(int|string $key): ?Tenant;

    /**
     * Get the name of this tenant provider
     *
     * Returns the name that this tenant provider was registered under.
     *
     * @return string
     */
    public function name(): string;
}
