<?php

declare(strict_types=1);

namespace Tenanted\Core\Resolvers;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Routing\RouteRegistrar;
use Illuminate\Support\Str;
use Tenanted\Core\Contracts\Tenancy;
use Tenanted\Core\Http\FallbackHandler;
use Tenanted\Core\Support\ParameterIdentityResolver;

/**
 * Subdomain Identity Resolver
 *
 * An implementation of {@see \Tenanted\Core\Contracts\IdentityResolver} that
 * deals with subdomains for the purpose of identification.
 */
class SubdomainIdentityResolver extends ParameterIdentityResolver
{
    /**
     * Predicate to check if an identifier should be excluded
     *
     * @var callable(string, string): bool|null
     */
    protected static $exclusionPredicate;

    /**
     * Provide a callback to check if an identifier should be excluded
     *
     * @param callable(string, string): bool $predicate
     *
     * @return void
     *
     * @noinspection PhpUnused
     */
    public static function excludeCallback(callable $predicate): void
    {
        self::$exclusionPredicate = $predicate;
    }

    /**
     * The domain that tenant identifiers are a subdomain of
     *
     * @var string
     */
    private string $domain;

    /**
     * @param string $name
     * @param string $domain
     */
    public function __construct(string $name, string $domain)
    {
        parent::__construct($name);

        $this->domain = $domain;
    }

    /**
     * Get the primary domain of the subdomains
     *
     * @return string
     */
    public function domain(): string
    {
        return $this->domain;
    }

    /**
     * @param \Illuminate\Http\Request         $request
     * @param \Tenanted\Core\Contracts\Tenancy $tenancy
     *
     * @return string|false|null
     */
    public function resolve(Request $request, Tenancy $tenancy): string|null|false
    {
        if ($request->route() !== null) {
            $identifier = parent::resolve($request, $tenancy);

            if ($identifier !== null && $identifier !== false) {
                $exclude = self::$exclusionPredicate;

                if ($exclude !== null && $exclude($identifier, $request->getHost())) {
                    return false;
                }
            }

            return $identifier;
        }

        // If there's no route, we're dealing with a route-less request, so we
        // need to handle that
        $host = $request->getHost();

        if (str_ends_with($host, '.' . $this->domain())) {
            return Str::before($host, '.' . $this->domain);
        }

        return false;
    }

    /**
     * @param \Illuminate\Routing\Router               $router
     * @param string                                   $tenancy
     * @param \Closure|\Closure[]|string|string[]|null $routes
     *
     * @return \Illuminate\Routing\RouteRegistrar
     */
    public function routes(Router $router, string $tenancy, array|Closure|string|null $routes = null): RouteRegistrar
    {
        return parent::routes($router, $tenancy)
            ->domain('{' . $this->getParameterName($tenancy) . '}.' . $this->domain())
            ->group(function (Router $router) use ($routes) {
                if ($routes !== null) {
                    $router->group([], $routes);
                }

                $router->fallback(FallbackHandler::class);
            })
            ->where([$this->getParameterName($tenancy) => '.*']);
    }
}
