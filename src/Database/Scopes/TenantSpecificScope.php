<?php

declare(strict_types=1);

namespace Tenanted\Database\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Tenanted\Core\Contracts\Tenancy;
use Tenanted\Core\TenantedManager;
use Tenanted\Database\Contracts\TenantRelationshipHandler;
use Tenanted\Database\Exceptions\EloquentRelationshipException;

/**
 * Tenant Specific Scope
 *
 * Eloquent global scope to handle tenant specific scoping of queries.
 */
class TenantSpecificScope implements Scope
{
    /**
     * @var \Tenanted\Core\TenantedManager
     */
    private TenantedManager $manager;

    /**
     * @param \Tenanted\Core\TenantedManager $manager
     */
    public function __construct(TenantedManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Get the tenant relationship handler used by the model
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return \Tenanted\Database\Contracts\TenantRelationshipHandler
     */
    private function getTenantRelationshipHandler(Model $model): TenantRelationshipHandler
    {
        if (! method_exists($model, 'getTenantRelationshipHandler')) {
            throw EloquentRelationshipException::missingMethod($model::class, 'getTenantRelationshipHandler');
        }

        return $model->getTenantRelationshipHandler();
    }

    /**
     * Get the tenancy the model is specific to
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return \Tenanted\Core\Contracts\Tenancy
     *
     * @throws \Tenanted\Core\Exceptions\TenancyException
     * @throws \Tenanted\Core\Exceptions\TenantProviderException
     */
    private function getTenancy(Model $model): Tenancy
    {
        if (! method_exists($model, 'getTenancyName')) {
            return $this->manager->tenancy();
        }

        return $this->manager->tenancy($model->getTenancyName());
    }

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \Illuminate\Database\Eloquent\Model   $model
     *
     * @return void
     *
     * @throws \Tenanted\Core\Exceptions\TenancyException
     * @throws \Tenanted\Core\Exceptions\TenantProviderException
     */
    public function apply(Builder $builder, Model $model): void
    {
        $handler = $this->getTenantRelationshipHandler($model);
        $tenancy = $this->getTenancy($model);

        $handler->scopeForQuery($model, $tenancy, $builder);
    }

    /**
     * Extend the query builder with the needed functions.
     *
     * @param \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model> $builder
     *
     * @return void
     */
    public function extend(Builder $builder): void
    {
        // Macro for querying against all entries
        $builder->macro('withoutTenant', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }
}
