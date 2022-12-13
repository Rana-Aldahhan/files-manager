<?php


namespace App\Services;

use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class CpuLoadWindowsCheck extends Check
{
    protected int $warningThreshold = 70;

    protected int $errorThreshold = 90;

    public function warnWhenCpuLoadIsAbovePercentage(int $percentage): self
    {
        $this->warningThreshold = $percentage;

        return $this;
    }

    public function failWhenCpuLoadIsAbovePercentage(int $percentage): self
    {
        $this->errorThreshold = $percentage;

        return $this;
    }
    /**
     * @return Result
     */
    public function run(): Result
    {
        $cpuLoad = $this->getCpuLoadPercentage();
        $result = Result::make()->meta(['cpu_load_percentage' => $cpuLoad]);
        if ($cpuLoad > $this->errorThreshold) {
            return $result->failed('the load is higer than ' . $this->errorThreshold . '%');
        } else if ($cpuLoad > $this->warningThreshold) {
            $result->warning('the load is higer than ' . $this->warningThreshold . '%');
        }
        return $result->ok('Ok cpu usage is ' . $cpuLoad . '%');
    }

    public function getCpuLoadPercentage()
    {
        $cmd = "wmic cpu get loadpercentage ";
        @exec($cmd, $output);
        return Intval($output[1]);
    }
}