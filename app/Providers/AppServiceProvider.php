<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\View\Components\RiskAlertCounter;
use App\View\Components\NotificationCounter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Registrar componentes personalizados
        Blade::component('risk-alert-counter', RiskAlertCounter::class);
        Blade::component('notification-counter', NotificationCounter::class);
    }
}
