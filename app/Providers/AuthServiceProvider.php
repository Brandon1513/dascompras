<?php

namespace App\Providers;

use App\Models\Requisicion;
use App\Models\Expediente;
use App\Policies\RequisicionPolicy;
use App\Policies\ExpedientePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Requisicion::class => RequisicionPolicy::class,
        Expediente::class  => ExpedientePolicy::class,
    ];

    public function boot(): void
    {
        // En Laravel 10+ ya no es necesario llamar registerPolicies()
        // $this->registerPolicies();

        // (Opcional) Si quieres dar acceso total a 'administrador' a cualquier ability:
        // Gate::before(fn ($user, $ability) => $user->hasRole('administrador') ? true : null);
    }
}
