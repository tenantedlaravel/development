<?php

declare(strict_types=1);

namespace Tenanted\Core\Events;

/**
 * Tenant Loaded Event
 *
 * This event is fired when a tenant is loaded using their key, before
 * they are set as the current tenant.
 *
 * @psalm-suppress MoreSpecificImplementedParamType
 * @psalm-suppress MethodSignatureMismatch
 */
final class TenantLoaded extends TenantResolved
{
}
