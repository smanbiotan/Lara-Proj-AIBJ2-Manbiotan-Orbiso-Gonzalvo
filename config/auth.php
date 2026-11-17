<?php

/*
 * Two completely separate authentication guards:
 *  - "customer": customers of the public website (table: customers)
 *  - "admin":    salon administrators (table: admins), used only under /admin
 * A customer session never grants access to the admin guard.
 */
return [
    'defaults' => ['guard' => 'customer', 'passwords' => 'customers'],

    'guards' => [
        'customer' => ['driver' => 'session', 'provider' => 'customers'],
        'admin' => ['driver' => 'session', 'provider' => 'admins'],
    ],

    'providers' => [
        'customers' => ['driver' => 'eloquent', 'model' => App\Models\Customer::class],
        'admins' => ['driver' => 'eloquent', 'model' => App\Models\Admin::class],
    ],

    'passwords' => [
        'customers' => ['provider' => 'customers', 'table' => 'password_reset_tokens', 'expire' => 60, 'throttle' => 60],
    ],

    'password_timeout' => 10800,
];
