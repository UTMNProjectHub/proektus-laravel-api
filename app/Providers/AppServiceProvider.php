<?php

namespace App\Providers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Gate;
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
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });

        Gate::define('admin', function (User $user) {
            return $user->hasRole('admin');
        });

        Gate::define('can-edit-project', function (User $user, Project $project) {
            return in_array($user->id, $project->users->pluck('id')->toArray()) || $user->hasRole('admin');
        });
    }
}
