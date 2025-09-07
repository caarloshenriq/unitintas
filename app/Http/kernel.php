<?php

namespace App\Http;

class Kernel
{
    protected $routeMiddleware = [
        'permission' => \App\Http\Middleware\PermissionMiddleware::class,
    ];
}
