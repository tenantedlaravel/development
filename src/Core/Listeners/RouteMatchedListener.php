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
        $route = $event->route;

        foreach ($route->middleware() as $item) {
            if ($item === TenantedRoute::ALIAS || Str::startsWith($item, TenantedRoute::ALIAS . ':')) {
                $options = explode(',', Str::after($item, ':'));

                if (! $this->manager->identify($event->request, $options[0] ?? null, $options[1] ?? null)) {
                    return;
                }
            }
        }
    }
}