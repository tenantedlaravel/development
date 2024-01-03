<?php

declare(strict_types=1);

namespace Tenanted\Database\Exceptions;

final class EloquentRelationshipException extends DatabaseException
{
    public static function invalid(string $model, string $relationName, string $tenancy): EloquentRelationshipException
    {
        return new self('The value of the \'' . $relationName . '\' relationship on the \'' . $model . '\' model, does not match the current tenant for the \'' . $tenancy . '\' tenancy');
    }

    public static function missingMethod(string $model, string $method): EloquentRelationshipException
    {
        return new self('The model \'' . $model . '\' is misconfigured, it is missing the \'' . $method . '\' method');
    }

    public static function noRelationship(string $model, string $relationship): EloquentRelationshipException
    {
        return new self('The relationship \'' . $relationship . '\' does not exist on the \'' . $model . '\' model');
    }

    public static function noCreation(string $model, string $relationship): EloquentRelationshipException
    {
        return new self('Could not create a relationship handler for the \'' . $relationship . '\' relationship on the \'' . $model . '\' model');
    }

    public static function unknownRelationship(string $type): EloquentRelationshipException
    {
        return new self('Could not create a relationship handler for the \'' . $type . '\' relation type');
    }
}
