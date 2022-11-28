<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Models\File;
use App\Models\Group;
use App\Policies\FilePolicy;
use App\Policies\GroupPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        File::class=>FilePolicy::class,
        Group::class=>GroupPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
