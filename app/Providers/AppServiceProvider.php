<?php

namespace App\Providers;

// use Illuminate\Support\Facades\URL; // Комментируем или удаляем, если не нужно для локальной разработки
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
        // if ($this->app->environment('production')) { // Комментируем или удаляем
        //     URL::forceScheme('https');
        // }
    }
}
