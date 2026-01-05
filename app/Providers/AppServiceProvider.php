<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\BatchModel;
use App\Models\UsedMaterialModel;
use App\Models\RawMaterialModel;
use App\Observers\BatchObserver;
use App\Observers\UsedMaterialObserver;
use App\Observers\RawMaterialObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        BatchModel::observe(BatchObserver::class);
        UsedMaterialModel::observe(UsedMaterialObserver::class);
        RawMaterialModel::observe(RawMaterialObserver::class);
    }
}
