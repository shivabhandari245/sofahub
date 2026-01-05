<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Console\Scheduling\Schedule;

class Kernel extends HttpKernel
{
    protected $routeMiddleware = [
   'role'=>\Spatie\Permission\Middleware\RoleMiddleware::class,
    'permission'=>\Spatie\Permission\Middleware\PermissionMiddleware::class,
    'block.approved' =>\App\Http\Middleware\BlockUntilApproved::class,
];



}
