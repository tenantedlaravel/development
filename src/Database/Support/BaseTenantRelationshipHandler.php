<?php

declare(strict_types=1);

namespace Tenanted\Database\Support;

use Illuminate\Database\Eloquent\Model;
use Tenanted\Core\Contracts\Tenancy;
use Tenanted\Database\Contracts\TenantRelationshipHandler;

abstract class BaseTenantRelationshipHandler implements TenantRelationshipHandler
{
    /**
     * Get the name of the relation
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param \Tenanted\Core\Contracts\Tenancy    $tenancy
     *
     * @return string
     */
    protected function getRelationName(Model $model, Tenancy $tenancy): string
    {
        if (method_exists($model, 'getTenantRelationshipName')) {
            return $model->getTenantRelationshipName();
        }

        return $tenancy->name();
    }

    /**
     * Get whether the model should function without a tenant
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return bool
     */
    protected function shouldFunctionWithoutTenant(Model $model): bool
    {
        if (method_exists($model, 'shouldFunctionWithoutTenant')) {
            return $model->shouldFunctionWithoutTenant();
        }

        return false;
    }

    /**
     * Get whether the model should scope to the tenant
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return bool
     */
    protected function shouldScopeToTenant(Model $model): bool
    {
        if (method_exists($model, 'shouldScopeToTenant')) {
            return $model->shouldScopeToTenant();
        }

        return false;
    }
}
