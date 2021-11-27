<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
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

        if (!$this->app->routesAreCached()) {
            Passport::routes();
        }

        Passport::tokensCan([
            'admin' => 'Admin access',
            'influencer' => 'Influencer access',
        ]);

        Gate::define('view', function (User $user, $model) {
            return $user->hasAccess("view-{$model}")
                || $user->hasAccess("create-{$model}")
                || $user->hasAccess("edit-{$model}");
        });

        Gate::define('create', function (User $user, $model) {
            return $user->hasAccess("create-{$model}");
        });

        Gate::define('edit', function (User $user, $model) {
            return $user->hasAccess("edit-{$model}");
        });

        Gate::define('delete', function (User $user, $model) {
            return $user->hasAccess("delete-{$model}");
        });
    }
}
