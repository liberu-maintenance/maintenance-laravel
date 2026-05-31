<?php

use DateInterval;

return [
    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    |
    | The model classes for permissions and roles.
    |
    */
    'models' => [
        'permission' => Spatie\Permission\Models\Permission::class,
        'role' => Spatie\Permission\Models\Role::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Table names
    |--------------------------------------------------------------------------
    |
    | The table names used by the package.
    |
    */
    'table_names' => [
        'roles' => 'roles',
        'permissions' => 'permissions',
        'model_has_permissions' => 'model_has_permissions',
        'model_has_roles' => 'model_has_roles',
        'role_has_permissions' => 'role_has_permissions',
    ],

    /*
    |--------------------------------------------------------------------------
    | Column names
    |--------------------------------------------------------------------------
    |
    | Customize the column names used by the package.
    |
    */
    'column_names' => [
        'model_morph_key' => 'model_id',
        'team_foreign_key' => 'team_id',
        'role_pivot_key' => 'role_id',
        'permission_pivot_key' => 'permission_id',
    ],

    /*
    |--------------------------------------------------------------------------
    | Teams
    |--------------------------------------------------------------------------
    |
    | Enable team support if required.
    |
    */
    'teams' => env('PERMISSION_TEAMS', false),

    /*
    |--------------------------------------------------------------------------
    | Display permission in exception
    |--------------------------------------------------------------------------
    |
    | When enabled the required permission names are added to exception messages.
    |
    */
    'display_permission_in_exception' => false,

    /*
    |--------------------------------------------------------------------------
    | Cache settings
    |--------------------------------------------------------------------------
    |
    | Configure cache expiration, key and store.
    |
    */
    'cache' => [
        'expiration_time' => DateInterval::createFromDateString('24 hours'),
        'key' => 'spatie.permission.cache',
        'store' => 'default',
    ],
];

