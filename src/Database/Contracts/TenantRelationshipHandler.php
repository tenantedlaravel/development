<?php

declare(strict_types=1);

namespace Tenanted\Database\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Tenanted\Core\Contracts\Tenancy;

/**
 * Relationship Handler Contract
 *
 * This contract defines the core functionality of a relationship handler, which
 * is responsible for handling the relation between a child mode and its parent
 * tenant model.
 */
interface TenantRelationshipHandler
{
    /**
     * Populate the tenant relationship when creating the model
     *
     * When creating a new entry in the database with a model, the tenant
     *  relationship will be automatically populated by this method.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param \Tenanted\Core\Contracts\Tenancy    $tenancy
     *
     * @return void
     */
    public function populateForCreation(Model $model, Tenancy $tenancy): void;

    /**
     * Populate the tenant relationship after retrieval
     *
     * Once a model has been retrieved from the database, the appropriate
     * tenant relationships will be populated based on the current tenant.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param \Tenanted\Core\Contracts\Tenancy    $tenancy
     *
     * @return void
     */
    public function populateAfterRetrieval(Model $model, Tenancy $tenancy): void;

    /**
     * Scope a query based on the current tenant
     *
     * Queries to the database for tenant owned models should be scoped to
     * the current tenant to avoid data leaks.
     *
     * @param \Illuminate\Database\Eloquent\Model   $model
     * @param \Tenanted\Core\Contracts\Tenancy      $tenancy
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return mixed
     */
    public function scopeForQuery(Model $model, Tenancy $tenancy, Builder $builder): Builder;
}
