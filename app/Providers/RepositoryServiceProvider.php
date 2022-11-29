<?php

namespace App\Providers;

use App\Interfaces\FileRepositoryInterface;
use App\Interfaces\GroupRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Repositories\Files\EloquentFileRepository;
use App\Repositories\Groups\EloquentGroupRepository;
use App\Repositories\Users\EloquentUserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(UserRepositoryInterface::class,EloquentUserRepository::class);
        $this->app->bind(FileRepositoryInterface::class,EloquentFileRepository::class);
        $this->app->bind(GroupRepositoryInterface::class,EloquentGroupRepository::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
