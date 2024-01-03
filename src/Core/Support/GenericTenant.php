<?php

declare(strict_types=1);

namespace Tenanted\Core\Support;

use Tenanted\Core\Contracts\Tenant;

final class GenericTenant implements Tenant
{
    /**
     * @var array<string, mixed>
     */
    private array $attributes;

    /**
     * @var string
     */
    private string $key;

    /**
     * @var string
     */
    private string $identifier;

    /**
     * @param array<string, mixed> $attributes
     * @param string               $key
     * @param string               $identifier
     */
    public function __construct(array $attributes = [], string $key = 'id', string $identifier = 'identifier')
    {
        $this->attributes = $attributes;
        $this->key        = $key;
        $this->identifier = $identifier;
    }

    /**
     * @param string $name
     *
     * @return mixed|null
     */
    public function __get(string $name)
    {
        return $this->attributes[$name] ?? null;
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return void
     */
    public function __set(string $name, mixed $value): void
    {
        // This is intentionally empty
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return array_key_exists($name, $this->attributes);
    }

    /**
     * @param string $name
     *
     * @return void
     */
    public function __unset(string $name): void
    {
        // This is intentionally empty
    }

    /**
     * @return string
     */
    public function getTenantIdentifier(): string
    {
        return $this->{$this->getTenantIdentifierName()};
    }

    /**
     * @return string
     */
    public function getTenantIdentifierName(): string
    {
        return $this->identifier;
    }

    /**
     * @return string|int
     */
    public function getTenantKey(): string|int
    {
        return $this->{$this->getTenantKeyName()};
    }

    /**
     * @return string
     */
    public function getTenantKeyName(): string
    {
        return $this->key;
    }
}
