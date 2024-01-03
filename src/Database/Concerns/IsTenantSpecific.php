<?php

declare(strict_types=1);

namespace Tenanted\Database\Concerns;

use Illuminate\Database\Eloquent\Model;
use Tenanted\Core\TenantedManager;
use Tenanted\Database\Contracts\TenantRelationshipHandler;
use Tenanted\Database\Exceptions\EloquentRelationshipException;
use Tenanted\Database\Scopes\TenantSpecificScope;
use Tenanted\Database\TenantedDatabaseManager;

/**
 * Is Tenant Specific trait
 *
 * This trait should be added to models that are tenant specific, allowing for
 * the automation of relationship handling and other features.
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait IsTenantSpecific
{
    /**
     * The relationship handler
     *
     * @var \Tenanted\Database\Contracts\TenantRelationshipHandler|null
     */
    private static ?TenantRelationshipHandler $handler = null;

    /**
     * Trait booting
     *
     * @return void
     */
    public static function bootIsTenantSpecific(): void
    {
        static::registerModelEvent('booted', self::handleModelBooted(...));

        self::creating(self::handleModelCreation(...));

        self::retrieved(self::handleModelRetrieval(...));

        self::addGlobalScope(app()->make(TenantSpecificScope::class));

    }

    /**
     * Handle the model 'booted' event
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return void
     */
    private static function handleModelBooted(Model $model): void
    {
        if (self::$handler === null) {
            if (! method_exists($model, 'getTenantRelationshipName')) {
                throw EloquentRelationshipException::missingMethod($model::class, 'getTenantRelationshipName');
            }

            self::$handler = app()->make(TenantedDatabaseManager::class)->relationship($model, $model->getTenantRelationshipName());
        }
    }

    /**
     * Handle the model 'creating' event
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return void
     *
     * @throws \Tenanted\Core\Exceptions\TenancyException
     * @throws \Tenanted\Core\Exceptions\TenantProviderException
     */
    private static function handleModelCreation(Model $model): void
    {
        if (! method_exists($model, 'getTenancyName')) {
            throw EloquentRelationshipException::missingMethod($model::class, 'getTenancyName');
        }

        $tenancy = app()->make(TenantedManager::class)->tenancy($model->getTenancyName());

        self::$handler->populateForCreation($model, $tenancy);
    }

    /**
     * Handle the model 'retrieved' event
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return void
     *
     * @throws \Tenanted\Core\Exceptions\TenancyException
     * @throws \Tenanted\Core\Exceptions\TenantProviderException
     */
    private static function handleModelRetrieval(Model $model): void
    {
        if (! method_exists($model, 'getTenancyName')) {
            throw EloquentRelationshipException::missingMethod($model::class, 'getTenancyName');
        }

        $tenancy = app()->make(TenantedManager::class)->tenancy($model->getTenancyName());

        self::$handler->populateAfterRetrieval($model, $tenancy);
    }

    /**
     * Get the tenant relationship handler used by this model
     *
     * @return \Tenanted\Database\Contracts\TenantRelationshipHandler
     */
    public function getTenantRelationshipHandler(): TenantRelationshipHandler
    {
        return self::$handler;
    }

    /**
     * Get the name of the tenancy this model belongs to
     *
     * @return string|null
     */
    public function getTenancyName(): ?string
    {
        return null;
    }

    /**
     * Get whether this models queries should be scoped to the current tenant
     *
     * @return bool
     */
    public function shouldScopeToTenant(): bool
    {
        return true;
    }

    /**
     * Get whether this model should function without a current tenant
     *
     * @return bool
     */
    public function shouldFunctionWithoutTenant(): bool
    {
        return true;
    }

    /**
     * Get the name of the relationship that relates to the tenant
     *
     * @return string
     */
    abstract public function getTenantRelationshipName(): string;
}
