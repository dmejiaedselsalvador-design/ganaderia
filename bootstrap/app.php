<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        // Las rutas web se registran por módulos en App\Providers\RouteServiceProvider
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Alias para permisos, roles y plantas
        $middleware->alias([
            'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'division'           => \App\Http\Middleware\EnsureDivisionAccess::class,
            'activo'             => \App\Http\Middleware\EnsureUserIsActive::class,
        ]);

        // Amplía la sesión cuando el usuario eligió "Mantener sesión iniciada"
        $middleware->web(prepend: [\App\Http\Middleware\ExtendRememberedSession::class]);
        // Expulsa a usuarios dados de baja en cualquier petición
        $middleware->web(append: [\App\Http\Middleware\EnsureUserIsActive::class]);

        // La cookie del tema la escribe el navegador (pantalla de login), no va cifrada
        $middleware->encryptCookies(except: ['tema']);

        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo('/');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Sesión/CSRF expirado (error 419): volver con un aviso en lugar de la pantalla de error
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() !== 419 || $request->expectsJson()) {
                return null;
            }

            if ($request->is('login')) {
                return redirect()->route('login')
                    ->withInput($request->except('password', '_token'))
                    ->withErrors(['email' => 'La página estuvo inactiva mucho tiempo. Intenta ingresar de nuevo.']);
            }

            return redirect()->back()
                ->withInput($request->except('password', 'password_confirmation', '_token'))
                ->with('aviso', 'Tu sesión había expirado. Revisa los datos y vuelve a enviar el formulario.');
        });
    })->create();
