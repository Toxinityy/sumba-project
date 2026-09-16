<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Anonymous component tags resolve "dotted.name" relative to the
        // path's root, so rooting this at resources/views (not .../layouts)
        // makes <x-layouts.site> resolve resources/views/layouts/site.blade.php
        // without moving the layout under resources/views/components.
        Blade::anonymousComponentPath(resource_path('views'));
    }
}
