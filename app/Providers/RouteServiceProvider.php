<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/admin/lista-usuarios';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            // API
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // 1. Dashboard, Perfil y Base
            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            // 2. Administración y Roles
            Route::middleware('web')
                ->group(base_path('routes/admin.php'));

            // 3. Hato Ganadero, Pesajes, Bajas y Expedientes
            Route::middleware('web')
                ->group(base_path('routes/ganado.php'));

            // 4. Proveedores, Guías, Liquidaciones y Anticipos
            Route::middleware('web')
                ->group(base_path('routes/proveedores.php'));

            // 5. Corrales y Lotes de Engorda (Feedlot)
            Route::middleware('web')
                ->prefix('corrales')
                ->name('corrales.')
                ->group(base_path('routes/corrales.php'));

            // 6. Nutrición, Raciones y Consumos
            Route::middleware('web')
                ->prefix('nutricion')
                ->name('nutricion.')
                ->group(base_path('routes/nutricion.php'));

            // 7. Calidad de Carne y Canales (Dra. Salud Rubio / Engormix)
            Route::middleware('web')
                ->prefix('calidad-carne')
                ->name('calidad.carne.')
                ->group(base_path('routes/calidad_carne.php'));

            // 8. Sanidad, Bioseguridad y Control de Retiro
            Route::middleware('web')
                ->prefix('sanidad')
                ->name('sanidad.')
                ->group(base_path('routes/sanidad.php'));

            // 9. Simulador Financiero y Rentabilidad (MXN)
            Route::middleware('web')
                ->group(base_path('routes/simulador.php'));

            // 10. Ventas y Liquidaciones Comerciales
            Route::middleware('web')
                ->prefix('ventas')
                ->name('ventas.')
                ->group(base_path('routes/ventas.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
