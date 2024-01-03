<?php

declare(strict_types=1);

namespace Tenanted\Core;

use Illuminate\Support\Arr;
use Tenanted\Core\Contracts\Tenant;
use Tenanted\Core\Contracts\TenantProvider;
use Tenanted\Core\Events\TenantChanged;
use Tenanted\Core\Events\TenantIdentified;
use Tenanted\Core\Events\TenantLoaded;

final class Tenancy implements Contracts\Tenancy
{
    /**
     * @var string
     */
    private string $name;

    /**
     * @var \Tenanted\Core\Contracts\TenantProvider
     */
    private TenantProvider $provider;

    /**
     * @var \Tenanted\Core\Contracts\Tenant|null
     */
    private ?Tenant $tenant;

    /**
     * @var string|null
     */
    private ?string $identifiedBy;

    /**
     * @var array<string, mixed>
     */
    private array $options;

    /**
     * @param string                                  $name
     * @param \Tenanted\Core\Contracts\TenantProvider $provider
     * @param array<string, mixed>                    $options
     */
    public function __construct(string $name, TenantProvider $provider, array $options = [])
    {
        $this->name     = $name;
        $this->provider = $provider;
        $this->options  = $options;
    }

    /**
     * @return bool
     */
    public function check(): bool
    {
        return $this->tenant !== null;
    }

    /**
     * @return \Tenanted\Core\Contracts\Tenant|null
     */
    public function tenant(): ?Tenant
    {
        return $this->tenant;
    }

    /**
     * @return string|null
     */
    public function identifier(): ?string
    {
        return $this->tenant()?->getTenantIdentifier();
    }

    /**
     * @return int|string|null
     */
    public function key(): int|string|null
    {
        return $this->tenant()?->getTenantKey();
    }

    /**
     * @return \Tenanted\Core\Contracts\TenantProvider
     */
    public function provider(): TenantProvider
    {
        return $this->provider;
    }

    /**
     * @param string $identifier
     * @param string $resolver
     *
     * @return bool
     */
    public function identify(string $identifier, string $resolver): bool
    {
        $tenant = $this->provider()->retrieveByIdentifier($identifier);

        if ($tenant !== null) {
            TenantIdentified::dispatch($tenant, $this, $resolver);

            if ($this->setTenant($tenant)) {
                $this->identifiedBy = $resolver;

                return true;
            }
        }

        return false;
    }

    /**
     * @param int|string $key
     *
     * @return bool
     */
    public function load(int|string $key): bool
    {
        $tenant = $this->provider()->retrieveByKey($key);

        if ($tenant !== null) {
            TenantLoaded::dispatch($tenant, $this);

            if ($this->setTenant($tenant)) {
                $this->identifiedBy = null;

                return true;
            }
        }

        return false;
    }

    /**
     * @param \Tenanted\Core\Contracts\Tenant|null $tenant
     *
     * @return bool
     */
    public function setTenant(?Tenant $tenant): bool
    {
        if ($this->tenant !== $tenant) {
            TenantChanged::dispatch($this, $this->tenant, $tenant);

            $this->tenant = $tenant;

            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function wasLoaded(): bool
    {
        return $this->check() && $this->identifiedBy() === null;
    }

    /**
     * @return bool
     */
    public function wasIdentified(): bool
    {
        return $this->check() && $this->identifiedBy() !== null;
    }

    /**
     * @return string|null
     */
    public function identifiedBy(): ?string
    {
        return $this->identifiedBy;
    }

    /**
     * @param string     $name
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function option(string $name, mixed $default = null): mixed
    {
        return Arr::get($this->options, $name, $default);
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }
}
