<?php

return [

    'resource_label' => 'Lookup Type',
    'resource_plural' => 'Lookup Types',
    'page_title' => 'Lookups',
    'no_types' => 'No lookup types defined. Run php artisan lookups:sync to create them from config.',

    'tenancy_mode' => [
        'shared' => 'Shared',
        'tenant' => 'Tenant',
        'both' => 'Both',
    ],

    'form' => [
        'type_details' => 'Type Details',
        'name' => 'Name',
        'slug' => 'Slug',
        'slug_helper' => 'Unique identifier used to reference this lookup type in code.',
        'description' => 'Description',
        'settings' => 'Settings',
        'is_hierarchical' => 'Hierarchical',
        'is_hierarchical_helper' => 'Enable parent-child relationships between values.',
        'is_active' => 'Active',
        'sort_order' => 'Sort Order',
        'tenancy_mode' => 'Tenancy Mode',
    ],

    'table' => [
        'name' => 'Name',
        'slug' => 'Slug',
        'is_hierarchical' => 'Hierarchical',
        'is_active' => 'Active',
        'tenancy_mode' => 'Tenancy Mode',
        'values_count' => 'Values',
        'created_at' => 'Created At',
    ],

    'filters' => [
        'is_active' => 'Status',
        'active' => 'Active',
        'inactive' => 'Inactive',
    ],

    'relation_manager' => [
        'title' => 'Lookup Values',
    ],

    'value_form' => [
        'name' => 'Name',
        'code' => 'Code',
        'code_helper' => 'Unique code within this lookup type for programmatic access.',
        'parent' => 'Parent Value',
        'no_parent' => 'No parent (root level)',
        'description' => 'Description',
        'is_active' => 'Active',
        'sort_order' => 'Sort Order',
        'metadata' => 'Metadata',
        'metadata_key' => 'Key',
        'metadata_value' => 'Value',
    ],

    'value_table' => [
        'name' => 'Name',
        'code' => 'Code',
        'parent' => 'Parent',
        'is_active' => 'Active',
        'sort_order' => 'Sort Order',
        'children_count' => 'Children',
    ],

];
