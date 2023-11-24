<?php

namespace Tenanted\Core\Contracts;

/**
 * Tenant Contract
 *
 * This contract marks a class as a tenant entity. Tenants are entities that
 * encompass/own everything that is considered part of a tenancy. They typically
 * sit at the top of an application's hierarchy, but they don't have to.
 */
interface Tenant
{
    /**
     * Get the unique tenant identifier
     *
     * Returns a unique string used to identify the tenant externally. This value
     * must be unique, and safe to share publicly.
     *
     * @return string
     */
    public function getTenantIdentifier(): string;

    /**
     * Get the name of the unique tenant identifier
     *
     * Returns the name of the unique identifier, whether that's a model attribute
     * name, property name, database column, or other.
     *
     * @return string
     */
    public function getTenantIdentifierName(): string;

    /**
     * Get the unique tenant key
     *
     * Returns a unique string or integer used to identify the tenant
     * internally. This value be unique and should be kept private. This is
     * typically the primary key within the database.
     *
     * @return string|int
     */
    public function getTenantKey(): string|int;

    /**
     * Get the name of the unique tenant key
     *
     * Returns the name of the unique key, whether that's a model attribute
     * name, property name, database column, or other.
     *
     * @return string
     */
    public function getTenantKeyName(): string;
}