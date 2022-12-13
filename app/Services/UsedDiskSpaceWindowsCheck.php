<?php


namespace App\Services;

use Illuminate\Support\Str;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class UsedDiskSpaceWindowsCheck extends Check
{
    protected int $warningThreshold = 70;

    protected int $errorThreshold = 90;

    protected ?string $filesystemName = null;

    public function filesystemName(string $filesystemName): self
    {
        $this->filesystemName = $filesystemName;

        return $this;
    }

    public function warnWhenUsedSpaceIsAbovePercentage(int $percentage): self
    {
        $this->warningThreshold = $percentage;

        return $this;
    }

    public function failWhenUsedSpaceIsAbovePercentage(int $percentage): self
    {
        $this->errorThreshold = $percentage;

        return $this;
    }

    public function run(): Result
    {
        $diskSpaceUsedPercentage = $this->getDiskUsagePercentage();
        $result = Result::make()
            ->meta(['disk_space_used_percentage' => $diskSpaceUsedPercentage])
            ->shortSummary($diskSpaceUsedPercentage . '%');

        if ($diskSpaceUsedPercentage > $this->errorThreshold) {
            return $result->failed("The disk is almost full ({$diskSpaceUsedPercentage}% used).");
        }

        if ($diskSpaceUsedPercentage > $this->warningThreshold) {
            return $result->warning("The disk is almost full ({$diskSpaceUsedPercentage}% used).");
        }

        return $result->ok('Ok disk usage is ' . $diskSpaceUsedPercentage . '%');
    }

    protected function getDiskUsagePercentage(): int
    {
        $cmd = "fsutil volume diskfree " . Str::substr(storage_path(), 0, 2);
        @exec($cmd, $output);
        $freeSpace = intval(Str::beforeLast(Str::between($output[0], '(', ')'), 'GB')); // free space in GB
        $totalSpace = intval(Str::beforeLast(Str::between($output[1], '(', ')'), 'GB')); // total space in GB
        $occupiedSpace = $totalSpace - $freeSpace;
        $usagePercentage = ($occupiedSpace / $totalSpace) * 100; // occupied space percentage
        return $usagePercentage;
    }
}