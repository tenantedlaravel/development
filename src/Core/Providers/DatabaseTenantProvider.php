<?php
declare(strict_types=1);

namespace Tenanted\Core\Providers;

use Illuminate\Database\ConnectionInterface;
use Tenanted\Core\Contracts\Tenant;
use Tenanted\Core\Support\BaseTenantProvider;
use Tenanted\Core\Support\GenericTenant;

/**
 * Database Tenant Provider
 *
 * An implementation of the {@see \Tenanted\Core\Contracts\TenantProvider}
 * contract providing support for using Laravels query builder.
 */
class DatabaseTenantProvider extends BaseTenantProvider
{
    /**
     * @var \Illuminate\Database\ConnectionInterface
     */
    private ConnectionInterface $connection;

    /**
     * @var string
     */
    private string $table;

    /**
     * @var string
     */
    private string $key;

    /**
     * @var string
     */
    private string $identifier;

    /**
     * @var class-string<\Tenanted\Core\Contracts\Tenant>
     */
    private string $entity;

    /**
     * @param string                                   $name
     * @param \Illuminate\Database\ConnectionInterface $connection
     * @param string                                   $table
     * @param string                                   $key
     * @param string                                   $identifier
     * @param string                                   $entity
     */
    public function __construct(string $name, ConnectionInterface $connection, string $table, string $key = 'id', string $identifier = 'identifier', string $entity = GenericTenant::class)
    {
        parent::__construct($name);
        $this->connection = $connection;
        $this->table      = $table;
        $this->key        = $key;
        $this->identifier = $identifier;
        $this->entity     = $entity;
    }

    /**
     * @param array $attributes
     *
     * @return \Tenanted\Core\Contracts\Tenant
     */
    protected function makeEntity(array $attributes): Tenant
    {
        return new $this->entity($attributes);
    }

    /**
     * @param string $identifier
     *
     * @return \Tenanted\Core\Contracts\Tenant|null
     */
    public function retrieveByIdentifier(string $identifier): ?Tenant
    {
        $attributes = $this->connection->table($this->table)
                                       ->where($this->identifier, '=', $identifier)
                                       ->first();

        if ($attributes !== null) {
            return $this->makeEntity((array) $attributes);
        }

        return null;
    }

    /**
     * @param int|string $key
     *
     * @return \Tenanted\Core\Contracts\Tenant|null
     */
    public function retrieveByKey(int|string $key): ?Tenant
    {
        $attributes = $this->connection->table($this->table)
                                       ->where($this->key, '=', $key)
                                       ->first();

        if ($attributes !== null) {
            return $this->makeEntity((array) $attributes);
        }

        return null;
    }
}