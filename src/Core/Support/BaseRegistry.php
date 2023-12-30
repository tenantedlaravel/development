<?php
declare(strict_types=1);

namespace Tenanted\Core\Support;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Tenanted\Core\Contracts\Registry;

/**
 * @template MClass of object
 *
 * @implements \Tenanted\Core\Contracts\Registry<MClass>
 */
abstract class BaseRegistry implements Registry
{
    /**
     * Custom creators
     *
     * @var array<string, callable(array<string, mixed>, string): MClass
     */
    protected static array $customCreators = [];

    /**
     * @param string                                         $name
     * @param callable(array<string, mixed>, string): MClass $creator
     *
     * @return void
     */
    public static function register(string $name, callable $creator): void
    {
        self::$customCreators[$name] = $creator;
    }

    /**
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected readonly Application $app;

    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected readonly Repository $config;

    /**
     * Instances
     *
     * @var array<string, MClass>
     */
    protected array $instances = [];

    public function __construct(Application $app, Repository $config)
    {
        $this->app    = $app;
        $this->config = $config;
    }
}