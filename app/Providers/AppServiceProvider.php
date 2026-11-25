<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        // Fechas relativas en español ("hace 5 minutos")
        \Carbon\Carbon::setLocale('es');

        // Política de claves: mínimo 8, mayúsculas, minúsculas y números
        \Illuminate\Validation\Rules\Password::defaults(fn () => \Illuminate\Validation\Rules\Password::min(8)
            ->letters()
            ->mixedCase()
            ->numbers());
    }
}
