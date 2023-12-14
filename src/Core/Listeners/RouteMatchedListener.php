<?php
declare(strict_types=1);

namespace Tenanted\Core\Listeners;

use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Str;
use Tenanted\Core\Http\Middleware\TenantedRoute;
use Tenanted\Core\TenantedManager;

class RouteMatchedListener
{
    /**
     * @var \Tenanted\Core\TenantedManager
     */
    private TenantedManager $manager;

    public function __construct(TenantedManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param \Illuminate\Routing\Events\RouteMatched $event
     *
     * @return void
     *
     * @throws \Tenanted\Core\Exceptions\IdentityResolverException
     * @throws \Tenanted\Core\Exceptions\TenancyException
     * @throws \Tenanted\Core\Exceptions\TenantProviderException
     */
    public function handle(RouteMatched $event): void
    {
        $route                    = $event->route;
        $identificationMiddleware = null;

        foreach ($route->middleware() as $middleware) {
            if ($middleware === TenantedRoute::ALIAS || Str::startsWith($middleware, TenantedRoute::ALIAS . ':')) {
                $identificationMiddleware = $middleware;
                break;
            }
        }

        $options = [];

        if (($identificationMiddleware !== null) && Str::contains($identificationMiddleware, ':')) {
            $options = explode(',', explode(':', $identificationMiddleware)[1]);
        }

        $this->manager->identify($event->request, $options[0] ?? null, $options[1] ?? null);
    }
}