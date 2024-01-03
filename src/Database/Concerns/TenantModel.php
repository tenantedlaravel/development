<?php

declare(strict_types=1);

namespace Tenanted\Database\Concerns;

/**
 * Tenant Model Trait
 *
 * This trait is for use on models that implement the {@see \Tenanted\Core\Contracts\Tenant}
 * contract. It provides a base implementation of it that should work for most
 * cases.
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 * @mixin \Tenanted\Core\Contracts\Tenant
 */
trait TenantModel
{
    /**
     * Get the unique tenant identifier
     *
     * Returns a unique string used to identify the tenant externally. This value
     * must be unique, and safe to share publicly.
     *
     * @return string
     */
    public function getTenantIdentifier(): string
    {
        return $this->getAttribute($this->getTenantIdentifierName());
    }

    /**
     * Get the name of the unique tenant identifier
     *
     * Returns the name of the unique identifier, whether that's a model attribute
     * name, property name, database column, or other.
     *
     * @return string
     */
    abstract public function getTenantIdentifierName(): string;

    /**
     * Get the unique tenant key
     *
     * Returns a unique string or integer used to identify the tenant
     * internally. This value be unique and should be kept private. This is
     * typically the primary key within the database.
     *
     * @return string|int
     */
    public function getTenantKey(): string|int
    {
        return $this->getKey();
    }

    /**
     * Get the name of the unique tenant key
     *
     * Returns the name of the unique key, whether that's a model attribute
     * name, property name, database column, or other.
     *
     * @return string
     */
    public function getTenantKeyName(): string
    {
        return $this->getKeyName();
    }
}
