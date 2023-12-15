<?php

namespace Tenanted\Core\Contracts;

/**
 * Tenancy Contract
 *
 * This contract represents the state of a tenancy, and is responsible for
 * keeping track of the current tenant, and how it became the current tenant.
 */
interface Tenancy
{
    /**
     * Check if there is a current tenant
     *
     * @return bool
     */
    public function check(): bool;

    /**
     * Get the current tenant
     *
     * @return \Tenanted\Core\Contracts\Tenant|null
     */
    public function tenant(): ?Tenant;

    /**
     * Get the identifier of the current tenant
     *
     * @return string|null
     */
    public function identifier(): ?string;

    /**
     * Get the key of the current tenant
     *
     * @return int|string|null
     */
    public function key(): int|string|null;

    /**
     * Get the tenant provider used to provide tenants for this tenancy
     *
     * @return \Tenanted\Core\Contracts\TenantProvider
     */
    public function provider(): TenantProvider;

    /**
     * Identify a tenant by its identifier
     *
     * This method takes an identifier and the name of an
     * {@see \Tenanted\Core\Contracts\IdentityResolver}. It will return true
     * if a tenant was successfully identified.
     *
     * This method should also dispatch a {@see \Tenanted\Core\Events\TenantIdentified}
     * event if a tenant is successfully identified.
     *
     * @param string $identifier
     * @param string $resolver
     *
     * @return bool
     */
    public function identify(string $identifier, string $resolver): bool;

    /**
     * Load a tenant by its key
     *
     * This method takes a key and will return true if a tenant was successfully
     * loaded.
     *
     * This method should also dispatch a {@see \Tenanted\Core\Events\TenantLoaded}
     * event if a tenant is successfully loaded.
     *
     * @param int|string $key
     *
     * @return bool
     */
    public function load(int|string $key): bool;

    /**
     * Set the current tenant
     *
     * This method will change the currently set tenant if the value provided
     * is different. This also includes "unsetting" the current tenant if null
     * is provided.
     *
     * This method should also dispatch a {@see \Tenanted\Core\Events\TenantChanged}
     * event if the current tenant is changed.
     *
     * @param \Tenanted\Core\Contracts\Tenant|null $tenant
     *
     * @return bool
     */
    public function setTenant(?Tenant $tenant): bool;

    /**
     * Check if the current tenant was loaded
     *
     * @return bool
     */
    public function wasLoaded(): bool;

    /**
     * Check if the current tenant was identified
     *
     * @return bool
     */
    public function wasIdentified(): bool;

    /**
     * Get the name of the identity resolver use to identify the current tenant
     *
     * @return string|null
     */
    public function identifiedBy(): ?string;

    /**
     * Get a tenancy option
     *
     * @param string     $name
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function option(string $name, mixed $default = null): mixed;

    /**
     * Get the name of this tenancy
     *
     *  Returns the name that this tenancy was registered under.
     *
     * @return string
     */
    public function name(): string;
}