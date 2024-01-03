<?php

declare(strict_types=1);

namespace Tenanted\Database\Relationships;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Tenanted\Core\Contracts\Tenancy;
use Tenanted\Database\Contracts\TenantRelationshipHandler;

class HasOneOrManyTenantRelationshipHandler implements TenantRelationshipHandler
{
    public function populateForCreation(Model $model, Tenancy $tenancy): void
    {
        // TODO: Implement populateForCreation() method.
    }

    public function populateAfterRetrieval(Model $model, Tenancy $tenancy): void
    {
        // TODO: Implement populateAfterLoading() method.
    }

    public function scopeForQuery(Model $model, Tenancy $tenancy, Builder $builder): Builder
    {
        // TODO: Implement scopeForQuery() method.
        return $builder;
    }
}
