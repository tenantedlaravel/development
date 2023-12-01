<?php
declare(strict_types=1);

namespace Tenanted\Core\Providers;

use Illuminate\Database\Eloquent\Model;
use Tenanted\Core\Contracts\Tenant;
use Tenanted\Core\Support\BaseTenantProvider;

/**
 * Eloquent Tenant Provider
 *
 * An implementation of the {@see \Tenanted\Core\Contracts\TenantProvider}
 * contract providing support for Eloquent models that are Tenants.
 */
class EloquentTenantProvider extends BaseTenantProvider
{
    /**
     * The Eloquent tenant model
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model&\Tenanted\Core\Contracts\Tenant>
     */
    private string $model;

    /**
     * Create a new eloquent user provider
     *
     * @param string          $name
     * @param class-string<\Illuminate\Database\Eloquent\Model&\Tenanted\Core\Contracts\Tenant> $model
     */
    public function __construct(string $name, string $model)
    {
        parent::__construct($name);
        $this->model = $model;
    }

    /**
     * Get the model class
     *
     * @return class-string<\Illuminate\Database\Eloquent\Model&\Tenanted\Core\Contracts\Tenant>
     *
     * @noinspection PhpUnused
     */
    public function getModelClass(): string
    {
        return $this->model;
    }

    /**
     * Get the model
     *
     * @return \Illuminate\Database\Eloquent\Model&\Tenanted\Core\Contracts\Tenant
     */
    public function getModel(): Model
    {
        /** @psalm-suppress UnsafeInstantiation */
        return new $this->model;
    }

    /**
     * @param string $identifier
     *
     * @return (\Illuminate\Database\Eloquent\Model&\Tenanted\Core\Contracts\Tenant)|null
     */
    public function retrieveByIdentifier(string $identifier): ?Tenant
    {
        $model = $this->getModel();

        /**
         * We have to disable this inspection because of the horrible return
         * types in the Laravel query builder.
         *
         * @noinspection PhpIncompatibleReturnTypeInspection
         * @noinspection UnknownInspectionInspection
         */
        return $model->newQuery()
                     ->where($model->getTenantIdentifierName(), '=', $identifier)
                     ->first();
    }

    /**
     * @param int|string $key
     *
     * @return (\Illuminate\Database\Eloquent\Model&\Tenanted\Core\Contracts\Tenant)|null
     */
    public function retrieveByKey(int|string $key): ?Tenant
    {
        $model = $this->getModel();

        /**
         * We have to disable this inspection because of the horrible return
         * types in the Laravel query builder.
         *
         * @noinspection PhpIncompatibleReturnTypeInspection
         * @noinspection UnknownInspectionInspection
         */
        return $model->newQuery()
                     ->where($model->getTenantKeyName(), '=', $key)
                     ->first();
    }
}