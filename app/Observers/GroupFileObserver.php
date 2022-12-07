<?php

namespace App\Observers;

use App\Models\GroupFile;
use Illuminate\Support\Facades\Cache;

class GroupFileObserver
{
    /**
     * Handle the GroupFile "created" event.
     *
     * @param  \App\Models\GroupFile  $groupFile
     * @return void
     */
    public function created(GroupFile $groupFile)
    {
        Cache::forget($groupFile->group_id);
    }

    /**
     * Handle the GroupFile "updated" event.
     *
     * @param  \App\Models\GroupFile  $groupFile
     * @return void
     */
    public function updated(GroupFile $groupFile)
    {
        Cache::forget($groupFile->group_id);
    }

    /**
     * Handle the GroupFile "deleted" event.
     *
     * @param  \App\Models\GroupFile  $groupFile
     * @return void
     */
    public function deleted(GroupFile $groupFile)
    {
        Cache::forget($groupFile->group_id);
    }

    /**
     * Handle the GroupFile "restored" event.
     *
     * @param  \App\Models\GroupFile  $groupFile
     * @return void
     */
    public function restored(GroupFile $groupFile)
    {
        Cache::forget($groupFile->group_id);
    }

    /**
     * Handle the GroupFile "force deleted" event.
     *
     * @param  \App\Models\GroupFile  $groupFile
     * @return void
     */
    public function forceDeleted(GroupFile $groupFile)
    {
        Cache::forget($groupFile->group_id);
    }
}