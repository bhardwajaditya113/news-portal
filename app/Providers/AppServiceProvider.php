<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
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
        Paginator::useBootstrap();

        // Load settings from database, but handle errors gracefully
        try {
            $setting = Setting::pluck('value', 'key')->toArray();
        } catch (\Exception $e) {
            // If database is not available (e.g., during package discovery), use empty array
            $setting = [];
        }

        View::composer('*', function($view) use ($setting){
            $view->with('settings', $setting);
        });
    }
}
