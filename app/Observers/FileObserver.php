<?php

namespace App\Observers;

use App\Models\File;
use Illuminate\Support\Facades\Cache;

class FileObserver
{
    private function emptyGroupCache(File $file)
    {

        $file->groups->map(function ($group) {
            Cache::forget($group->id);
        });
    }
    /**
     * Handle the File "created" event.
     *
     * @param  \App\Models\File  $file
     * @return void
     */
    public function created(File $file)
    {
        $this->emptyGroupCache($file);
    }

    /**
     * Handle the File "updated" event.
     *
     * @param  \App\Models\File  $file
     * @return void
     */
    public function updated(File $file)
    {
        $this->emptyGroupCache($file);
    }

    /**
     * Handle the File "deleted" event.
     *
     * @param  \App\Models\File  $file
     * @return void
     */
    public function deleted(File $file)
    {
        $this->emptyGroupCache($file);
    }

    /**
     * Handle the File "restored" event.
     *
     * @param  \App\Models\File  $file
     * @return void
     */
    public function restored(File $file)
    {
        $this->emptyGroupCache($file);
    }

    /**
     * Handle the File "force deleted" event.
     *
     * @param  \App\Models\File  $file
     * @return void
     */
    public function forceDeleted(File $file)
    {
        $this->emptyGroupCache($file);
    }
}