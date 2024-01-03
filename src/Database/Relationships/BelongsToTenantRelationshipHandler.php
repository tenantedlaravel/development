<?php

declare(strict_types=1);

namespace Tenanted\Database\Relationships;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Tenanted\Core\Contracts\Tenancy;
use Tenanted\Core\Contracts\Tenant;
use Tenanted\Core\Exceptions\TenantNotFoundException;
use Tenanted\Database\Exceptions\EloquentRelationshipException;
use Tenanted\Database\Support\BaseTenantRelationshipHandler;

/**
 * Belongs To Tenant Relationship Handler
 *
 * This relationship handler implements the {@see \Tenanted\Database\Contracts\TenantRelationshipHandler}
 * contract, and handles relationships where a model belongs to a
 * {@see \Tenanted\Core\Contracts\Tenant}.
 */
class BelongsToTenantRelationshipHandler extends BaseTenantRelationshipHandler
{
    /**
     * Validate the current value of the relationship
     *
     * @param string                              $relationName
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param \Tenanted\Core\Contracts\Tenancy    $tenancy
     *
     * @return void
     *
     * @throws \Tenanted\Database\Exceptions\EloquentRelationshipException
     */
    private function validateCurrentValue(string $relationName, Model $model, Tenancy $tenancy): void
    {
        if ($model->relationLoaded($relationName)) {
            $loaded = $model->getRelation($relationName);

            if (! ($loaded instanceof Tenant) || $loaded->getTenantKey() !== $tenancy->key()) {
                throw EloquentRelationshipException::invalid($model::class, $relationName, $tenancy->name());
            }
        } else {
            /**
             * @var \Illuminate\Database\Eloquent\Relations\BelongsTo $relation
             */
            $relation = $model->{$relationName}();
            $key      = $model->getAttribute($relation->getForeignKeyName());

            if ($tenancy->key() !== $key) {
                throw EloquentRelationshipException::invalid($model::class, $relationName, $tenancy->name());
            }
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param \Tenanted\Core\Contracts\Tenancy    $tenancy
     *
     * @return void
     *
     * @throws \Tenanted\Core\Exceptions\TenantNotFoundException
     * @throws \Tenanted\Database\Exceptions\EloquentRelationshipException
     */
    public function populateForCreation(Model $model, Tenancy $tenancy): void
    {
        if (! $tenancy->check()) {
            if ($this->shouldFunctionWithoutTenant($model)) {
                return;
            }

            throw TenantNotFoundException::none($tenancy->name());
        }

        $relationName = $this->getRelationName($model, $tenancy);

        $this->validateCurrentValue($relationName, $model, $tenancy);

        $model->{$relationName}()->associate($tenancy->tenant());
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param \Tenanted\Core\Contracts\Tenancy    $tenancy
     *
     * @return void
     *
     * @throws \Tenanted\Core\Exceptions\TenantNotFoundException
     * @throws \Tenanted\Database\Exceptions\EloquentRelationshipException
     */
    public function populateAfterRetrieval(Model $model, Tenancy $tenancy): void
    {
        if (! $tenancy->check()) {
            if ($this->shouldFunctionWithoutTenant($model)) {
                return;
            }

            throw TenantNotFoundException::none($tenancy->name());
        }

        $relationName = $this->getRelationName($model, $tenancy);

        $this->validateCurrentValue($relationName, $model, $tenancy);

        $model->setRelation($relationName, $tenancy->tenant());
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model   $model
     * @param \Tenanted\Core\Contracts\Tenancy      $tenancy
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     *
     * @throws \Tenanted\Core\Exceptions\TenantNotFoundException
     */
    public function scopeForQuery(Model $model, Tenancy $tenancy, Builder $builder): Builder
    {
        if (! $this->shouldScopeToTenant($model)) {
            return $builder;
        }

        if (! $tenancy->check()) {
            throw TenantNotFoundException::none($tenancy->name());
        }

        $relationName = $this->getRelationName($model, $tenancy);

        /**
         * @var \Illuminate\Database\Eloquent\Relations\BelongsTo $relation
         */
        $relation = $model->{$relationName}();

        return $builder->where(
            $relation->getForeignKeyName(),
            '=',
            $tenancy->key()
        );
    }
}
