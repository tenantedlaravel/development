<?php

declare(strict_types=1);

namespace Tenanted\Database;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Str;
use Tenanted\Database\Contracts\TenantRelationshipHandler;
use Tenanted\Database\Exceptions\EloquentRelationshipException;
use Tenanted\Database\Relationships\BelongsToManyTenantRelationshipHandler;
use Tenanted\Database\Relationships\BelongsToTenantRelationshipHandler;
use Tenanted\Database\Relationships\HasManyThroughTenantRelationshipHandler;
use Tenanted\Database\Relationships\HasOneOrManyTenantRelationshipHandler;
use Tenanted\Database\Relationships\HasOneOrMorphOneTenantRelationshipHandler;

final class TenantedDatabaseManager
{
    public final const RELATION_MAP = [
        BelongsTo::class      => 'BelongsTo',
        BelongsToMany::class  => 'BelongsToMany',
        HasOne::class         => 'HasOneOrMorphOne',
        MorphOne::class       => 'HasOneOrMorphOne',
        HasOneOrMany::class   => 'HasOneOrMany',
        HasManyThrough::class => 'HasManyThrough',
    ];

    /**
     * Custom relationship handler creators
     *
     * @var array<class-string<\Illuminate\Database\Eloquent\Relations\Relation>, callable(\Illuminate\Database\Eloquent\Model):\Tenanted\Database\Contracts\TenantRelationshipHandler>
     */
    private static array $customRelationshipHandlerCreators = [];

    /**
     * Register a custom relationship handler creator
     *
     * @param string                                                                  $relation
     * @param callable(\Illuminate\Database\Eloquent\Model):TenantRelationshipHandler $creator
     *
     * @return void
     */
    public static function registerRelationshipHandler(string $relation, callable $creator): void
    {
        self::$customRelationshipHandlerCreators[$relation] = $creator;
    }

    /**
     * Relationship handler instances
     *
     * @var array<class-string<\Illuminate\Database\Eloquent\Relations\Relation, \Tenanted\Database\Contracts\TenantRelationshipHandler>
     */
    private array $relationshipHandlers = [];

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string                              $relationship
     *
     * @return \Tenanted\Database\Contracts\TenantRelationshipHandler
     *
     * @throws \Tenanted\Database\Exceptions\EloquentRelationshipException
     */
    public function relationship(Model $model, string $relationship): TenantRelationshipHandler
    {
        if (! $model->isRelation($relationship)) {
            throw EloquentRelationshipException::noRelationship($model::class, $relationship);
        }

        $relation = $model->{$relationship}();

        if (self::$customRelationshipHandlerCreators[$relation::class]) {
            return self::$customRelationshipHandlerCreators[$relation::class]($model);
        }

        foreach (self::RELATION_MAP as $class => $map) {
            if ($relation instanceof $class) {
                return $this->createRelationshipHandler($map, $model);
            }
        }

        throw EloquentRelationshipException::noCreation($model::class, $relationship);
    }

    public function createRelationshipHandler(string $type, Model $model)
    {
        if (isset($this->relationshipHandlers[$type])) {
            return $this->relationshipHandlers[$type];
        }

        $method = 'create' . Str::camel($type) . 'RelationshipHandler';

        if (method_exists($this, $method)) {
            $this->{$method}($model);
        }

        throw EloquentRelationshipException::unknownRelationship($type);
    }

    /**
     * Create a new instance of the belongs to relationship handler
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return \Tenanted\Database\Relationships\BelongsToTenantRelationshipHandler
     */
    private function createBelongsToRelationshipHandler(Model $model): BelongsToTenantRelationshipHandler
    {
        return new BelongsToTenantRelationshipHandler();
    }

    /**
     * Create a new instance of the belongs to many relationship handler
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return \Tenanted\Database\Relationships\BelongsToManyTenantRelationshipHandler
     */
    private function createBelongsToManyRelationshipHandler(Model $model): BelongsToManyTenantRelationshipHandler
    {
        return new BelongsToManyTenantRelationshipHandler();
    }

    /**
     * Create a new instance of the has one or morph one relationship handler
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return \Tenanted\Database\Relationships\HasOneOrMorphOneTenantRelationshipHandler
     */
    private function createHasOneOrMorphOneRelationshipHandler(Model $model): HasOneOrMorphOneTenantRelationshipHandler
    {
        return new HasOneOrMorphOneTenantRelationshipHandler();
    }

    /**
     * Create a new instance of the has one or many relationship handler
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return \Tenanted\Database\Relationships\HasOneOrManyTenantRelationshipHandler
     */
    private function createHasOneOrManyRelationshipHandler(Model $model): HasOneOrManyTenantRelationshipHandler
    {
        return new HasOneOrManyTenantRelationshipHandler();
    }

    /**
     * Create a new instance of the has many through relationship handler
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return \Tenanted\Database\Relationships\HasManyThroughTenantRelationshipHandler
     */
    private function createHasManyThroughRelationshipHandler(Model $model): HasManyThroughTenantRelationshipHandler
    {
        return new HasManyThroughTenantRelationshipHandler();
    }
}
