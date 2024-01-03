<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Tenanted Defaults
    |--------------------------------------------------------------------------
    |
    | This option controls the default tenanted options, such as provider,
    | tenancy, and resolver. You may change these defaults as required.
    |
    */

    'defaults' => [
        'provider' => 'tenants',
        'tenancy'  => 'primary',
    ],

    /*
    |--------------------------------------------------------------------------
    | Tenancies
    |--------------------------------------------------------------------------
    |
    | Next, you may define every tenancy for your application.
    | Of course, a great default configuration has been defined for you
    | here which uses subdomain identification and the Eloquent tenant provider.
    |
    | All tenancies have a tenant provider. This defines how the
    | tenants are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your tenant's data.
    |
    */

    'tenancies' => [
        'primary' => [
            'provider' => 'tenants',
            'options'  => [],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Tenant Providers
    |--------------------------------------------------------------------------
    |
    | All tenancies have a tenant provider. This defines how the
    | tenants are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your tenant's data.
    |
    | If you have multiple tenant tables or models you may configure multiple
    | sources which represent each model / table. These sources may then
    | be assigned to any extra tenancies you have defined.
    |
    | Supported drivers: "database", "eloquent"
    |
    */

    'providers' => [
        'tenants' => [
            'driver' => 'eloquent',
            'model'  => \App\Models\Tenant::class,
        ],

        // 'tenants' => [
        //     'driver'     => 'database',
        //     'connection' => env('DB_CONNECTION'),
        //     'table'      => 'tenants',
        //     'key'        => 'id',
        //     'identifier' => 'identifier',
        //     'entity'     => \Tenanted\Core\Support\GenericTenant::class,
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Identity Resolvers
    |--------------------------------------------------------------------------
    |
    | Identity resolvers are responsible for retrieving a tenant identifier
    | from a request.
    |
    | If you have multiple ways of identifying a tenant, you may configure
    | multiple resolvers. Tenancies are able to use any configured resolver.
    |
    | Supported drivers: "subdomain", "path", "header"
    |
    */

    'resolvers' => [
        'subdomain' => [
            'driver' => 'subdomain',
            'domain' => env('TENANT_DOMAIN'),
        ],

        'path' => [
            'driver'  => 'path',
            'segment' => 0,
        ],

        'header' => [
            'driver' => 'header',
            'header' => 'Tenant-Identifier',
        ],
    ],

];
