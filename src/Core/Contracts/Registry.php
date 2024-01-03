<?php

declare(strict_types=1);

namespace Tenanted\Core\Contracts;

/**
 * Registry Contract
 *
 * This contract allows for the simple creation of a "registry" where classes
 * of a particular type are registered and retrieved.
 *
 * @template MClass of object
 */
interface Registry
{
    /**
     * Register a custom creator with this registry
     *
     * @template SMClass of MClass
     *
     * @param string                                          $name
     * @param callable(array<string, mixed>, string): SMClass $creator
     *
     * @return void
     */
    public static function register(string $name, callable $creator): void;

    /**
     * Get an instance of this registries' class
     *
     * @param string|null $name
     *
     * @return object
     *
     * @psalm-return MClass
     * @phpstan-return MClass
     */
    public function get(?string $name = null): object;
}
