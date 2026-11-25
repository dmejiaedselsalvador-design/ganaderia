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
    public const HOME = '/';

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

            // Autenticación, portal de plantas, perfil, tema y administración (comunes a todas las plantas)
            Route::middleware('web')->group(base_path('routes/web.php'));
            Route::middleware('web')->group(base_path('routes/admin.php'));

            /*
            |------------------------------------------------------------------
            | PLANTA: GANADERÍA  (middleware division:ganaderia)
            |------------------------------------------------------------------
            | Cada módulo exige además su permiso (can:ganaderia.<modulo>).
            */
            $ganaderia = ['web', 'auth', 'division:ganaderia'];

            // Dashboard ganadero
            Route::middleware($ganaderia)
                ->group(base_path('routes/ganaderia.php'));

            // Hato Ganadero, Pesajes, Bajas y Expedientes
            Route::middleware([...$ganaderia, 'can:ganaderia.ganado'])
                ->group(base_path('routes/ganado.php'));

            // Proveedores, Guías, Liquidaciones y Anticipos
            Route::middleware([...$ganaderia, 'can:ganaderia.proveedores'])
                ->group(base_path('routes/proveedores.php'));

            // Corrales y Lotes de Engorda (Feedlot)
            Route::middleware([...$ganaderia, 'can:ganaderia.corrales'])
                ->prefix('corrales')
                ->name('corrales.')
                ->group(base_path('routes/corrales.php'));

            // Nutrición, Raciones y Consumos
            Route::middleware([...$ganaderia, 'can:ganaderia.nutricion'])
                ->prefix('nutricion')
                ->name('nutricion.')
                ->group(base_path('routes/nutricion.php'));

            // Calidad de Carne y Canales
            Route::middleware([...$ganaderia, 'can:ganaderia.calidad'])
                ->prefix('calidad-carne')
                ->name('calidad.carne.')
                ->group(base_path('routes/calidad_carne.php'));

            // Sanidad, Bioseguridad y Control de Retiro
            Route::middleware([...$ganaderia, 'can:ganaderia.sanidad'])
                ->prefix('sanidad')
                ->name('sanidad.')
                ->group(base_path('routes/sanidad.php'));

            // Simulador Financiero y Rentabilidad (MXN)
            Route::middleware([...$ganaderia, 'can:ganaderia.simulador'])
                ->group(base_path('routes/simulador.php'));

            // Ventas y Liquidaciones Comerciales
            Route::middleware([...$ganaderia, 'can:ganaderia.ventas'])
                ->prefix('ventas')
                ->name('ventas.')
                ->group(base_path('routes/ventas.php'));

            /*
            |------------------------------------------------------------------
            | PLANTA: CONSTRUCCIÓN  (middleware division:construccion)
            |------------------------------------------------------------------
            */
            Route::middleware(['web', 'auth', 'division:construccion'])
                ->prefix('construccion')
                ->name('construccion.')
                ->group(base_path('routes/construccion.php'));
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
