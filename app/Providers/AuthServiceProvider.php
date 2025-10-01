<?php

namespace App\Providers;

use App\Models\Requisicion;
use App\Policies\RequisicionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Requisicion::class => RequisicionPolicy::class,
    ];

    public function boot(): void
    {
        // En Laravel 10+ ya no es necesario registerPolicies(),
        // pero no hace daño si lo llamas:
        // $this->registerPolicies();
    }
}
