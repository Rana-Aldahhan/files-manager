<?php

namespace App\Providers;

use App\Models\File;
use App\Models\GroupFile;
use App\Models\GroupUser;
use App\Observers\FileObserver;
use App\Observers\GroupFileObserver;
use App\Observers\GroupUserObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        File::observe(FileObserver::class);
        GroupUser::observe(GroupUserObserver::class);
        GroupFile::observe(GroupFileObserver::class);
    }
}