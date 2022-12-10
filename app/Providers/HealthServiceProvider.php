<?php

namespace App\Providers;

use App\Services\CpuLoadWindowsCheck;
use App\Services\UsedDiskSpaceWindowsCheck;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Spatie\CpuLoadHealthCheck\CpuLoadCheck;
use Spatie\Health\Facades\Health;
use Spatie\Health\Checks\Checks\UsedDiskSpaceCheck;
use Spatie\Health\Checks\Checks\DatabaseCheck;
use Spatie\Health\Checks\Checks\CacheCheck;
use Spatie\Health\Checks\Checks\DatabaseConnectionCountCheck;
use Spatie\Health\Checks\Checks\DatabaseTableSizeCheck;
use Spatie\Health\Checks\Checks\DebugModeCheck;
use Spatie\Health\Checks\Checks\EnvironmentCheck;
use Spatie\SecurityAdvisoriesHealthCheck\SecurityAdvisoriesCheck;

class HealthServiceProvider extends ServiceProvider
{
    private $windowsOS;
    public function __construct($app)
    {
        parent::__construct($app);
        $this->windowsOS = false;
        if (Str::of(PHP_OS)->contains("win", true)) {
            $this->windowsOS = true;
        }
    }
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $OSDependChecks = null;
        if ($this->windowsOS) {
            $OSDependChecks = [
                UsedDiskSpaceWindowsCheck::new()->warnWhenUsedSpaceIsAbovePercentage(60),
                CpuLoadWindowsCheck::new()->warnWhenCpuLoadIsAbovePercentage(50),
            ];
        } else {
            // linux based os,
            $OSDependChecks = [
                UsedDiskSpaceCheck::new(),
                CpuLoadCheck::new()->failWhenLoadIsHigherInTheLast5Minutes(2.0),
            ];
        }
        $OSInedpendChecks = [
            DatabaseCheck::new(),
            CacheCheck::new(),
            EnvironmentCheck::new(),
            DebugModeCheck::new(),
            DatabaseConnectionCountCheck::new()->failWhenMoreConnectionsThan(100),
            DatabaseTableSizeCheck::new()->table('files', maxSizeInMb: 2_000)->table('request_logs', maxSizeInMb: 2_00),
            SecurityAdvisoriesCheck::new(),
        ];
        $checks = Arr::collapse([$OSDependChecks, $OSInedpendChecks]);
        Health::checks($checks);
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
