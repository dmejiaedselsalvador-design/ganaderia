<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Súper Admin y Dueño tienen todos los permisos. Las protecciones especiales
        // (p. ej. que el Dueño no pueda eliminar al Súper Admin) se validan en
        // App\Http\Controllers\Admin\UserController.
        Gate::before(function ($user, string $ability) {
            return method_exists($user, 'tieneAccesoTotal') && $user->tieneAccesoTotal() ? true : null;
        });
    }
}
