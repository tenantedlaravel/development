<?php
declare(strict_types=1);

namespace Tenanted\Core\Support;

use Tenanted\Core\Contracts\Tenant;

final class GenericTenant implements Tenant
{
    private array $attributes;

    private string $key;

    private string $identifier;

    public function __construct(array $attributes = [], string $key = 'id', string $identifier = 'identifier')
    {
        $this->attributes = $attributes;
        $this->key        = $key;
        $this->identifier = $identifier;
    }

    public function __get(string $name)
    {
        return $this->attributes[$name] ?? null;
    }

    public function __set(string $name, $value): void
    {
        // This is intentionally empty
    }

    public function __isset(string $name): bool
    {
        return array_key_exists($name, $this->attributes);
    }

    public function __unset(string $name): void
    {
        // This is intentionally empty
    }

    public function getTenantIdentifier(): string
    {
        return $this->{$this->getTenantIdentifierName()};
    }

    public function getTenantIdentifierName(): string
    {
        return $this->identifier;
    }

    public function getTenantKey(): string|int
    {
        return $this->{$this->getTenantKeyName()};
    }

    public function getTenantKeyName(): string
    {
        return $this->key;
    }
}