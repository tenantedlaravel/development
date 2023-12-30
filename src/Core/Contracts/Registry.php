<?php

namespace Tenanted\Core\Contracts;

/**
 * @template MClass of object
 */
interface Registry
{
    /**
     * Register a custom creator with this registry
     *
     * @param string                                         $name
     * @param callable(array<string, mixed>, string): MClass $creator
     *
     * @return void
     */
    public static function register(string $name, callable $creator): void;

    /**
     * Get an instance of this registries' class
     *
     * @param string $name
     *
     * @return object
     *
     * @psalm-return MClass
     * @phpstan-return MClass
     */
    public function get(string $name): object;
}