<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Lookup Classes
    |--------------------------------------------------------------------------
    |
    | Path and namespace where your Lookup classes live. Create them with
    | `php artisan make:lookup` and sync to the database with
    | `php artisan lookups:sync`.
    |
    */
    'lookups_path' => app_path('Lookups'),
    'lookups_namespace' => 'App\\Lookups',

    /*
    |--------------------------------------------------------------------------
    | Table Names
    |--------------------------------------------------------------------------
    |
    | Customize the database table names used by this package.
    |
    */
    'tables' => [
        'lookup_types' => 'lookup_types',
        'lookup_values' => 'lookup_values',
    ],

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    |
    | Override the default models used by this package with your own.
    |
    */
    'models' => [
        'lookup_type' => \Wezlo\FilamentLookups\Models\LookupType::class,
        'lookup_value' => \Wezlo\FilamentLookups\Models\LookupValue::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Multi-Tenancy
    |--------------------------------------------------------------------------
    |
    | Enable multi-tenancy to scope lookup types and values per tenant.
    | Each lookup type declares its own tenancy_mode (shared, tenant, both).
    | This global toggle controls whether tenancy features are active.
    |
    */
    'tenancy' => [
        'enabled' => false,
        'tenant_model' => null,
        'tenant_id_column' => 'tenant_id',
    ],

    /*
    |--------------------------------------------------------------------------
    | Navigation
    |--------------------------------------------------------------------------
    |
    | Configure where the lookup management page appears in the panel.
    |
    */
    'navigation_group' => 'Settings',
    'navigation_icon' => null,
    'navigation_sort' => null,

    /*
    |--------------------------------------------------------------------------
    | Page Registration
    |--------------------------------------------------------------------------
    |
    | Control whether the Filament page is registered.
    |
    */
    'register_resource' => true,

];
